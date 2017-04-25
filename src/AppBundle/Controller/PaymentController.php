<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Department;
use AppBundle\Entity\Photo;
use AppBundle\Entity\Product;
use AppBundle\Entity\StoreOrder;
use AppBundle\Entity\StoreOrderDelivery;
use AppBundle\Entity\StoreOrderProduct;
use AppBundle\Form\StoreOrderType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\Store;

/**
 * Class PaymentController
 * @package AppBundle\Controller
 * @author Plamen Markov <plamen@lynxlake.org>
 *
 * @Route("payment")
 */
class PaymentController extends Controller
{
    /**
     * Display payment form.
     *
     * @Route("/", name="payment_index")
     * @Method({"GET","POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $cart = $this->get('app.common')->getSessionCart($request);
        if ($cart['qty'] == 0) {
            return $this->redirectToRoute('store_index');
        }

        if ($this->getUser() == null) {
            return $this->redirectToRoute('checkout_authorize');
        }

        /**
         * @var \Doctrine\Common\Persistence\ObjectManager $em
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * @var \AppBundle\Entity\User $user
         */
        $user = $this->getUser();

        $storeOrder = new StoreOrder();
        $storeOrder->setBillingFirstName($user->getFirstName());
        $storeOrder->setBillingLastName($user->getLastName());
        $storeOrder->setBillingEmail($user->getEmail());
        $storeOrder->setBillingPhone($user->getPhone());
        $storeOrder->setBillingAddress($user->getAddress());
        $storeOrder->setBillingCity($user->getCity());
        $storeOrder->setBillingState($user->getState());
        $storeOrder->setBillingZip($user->getZip());
        $form = $this->createForm(StoreOrderType::class, $storeOrder, ['states' => $this->get('app.common')->getStates()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $errors = [];
            $creditCardNumber = $form['creditCardNumber']->getData();
            if (!$this->isCardNumberValid($creditCardNumber)) {
                $errors['creditCardNumber'] = 'Invalid credit card number.';
            }

            $creditCardCode = $form['creditCardCode']->getData();
            if (4 < strlen($creditCardCode) || 3 > strlen($creditCardCode)) {
                $errors['creditCardCode'] = 'Invalid security code.';
            }

            $creditCardYear = (int)$form['creditCardYear']->getData();
            $creditCardMonth = (int)$form['creditCardMonth']->getData();
            if ($this->isCardExpired($creditCardMonth, $creditCardYear)) {
                $errors['creditCardMonth'] = 'Credit card is already expired.';
            }

            if (count($errors)) {
                // Attach errors to form fields
                foreach ($errors as $field => $message) {
                    $error = new FormError($message);
                    $form->get($field)->addError($error);
                }

            } else {
                $billingAddress2 = (isset($form['billingAddress2'])) ? trim($form['billingAddress2']->getData()) : '';
                $shippingAddress2 = (isset($form['shippingAddress2'])) ? trim($form['shippingAddress2']->getData()) : '';

                $storeOrder->setBillingAddress2($billingAddress2);
                $storeOrder->setShippingAddress2($shippingAddress2);
                $storeOrder->setUser($this->getUser());
                $storeOrder->setUserId($this->getUser()->getId());
                $storeOrder->setIpAddress($request->getClientIp());

                $creditCardExpDate = sprintf("%04d-%02d", $creditCardYear, $creditCardMonth);
                $storeOrder->setCreditCardExpDate($creditCardExpDate);

                if (!empty($cart['items'])) {
                    $storeOrder->setQty($cart['qty']);
                    $storeOrder->setSubtotal($cart['subtotal']);
                    $storeOrder->setTaxes($cart['taxes']);
                    $storeOrder->setDelivery($cart['delivery']);
                    $storeOrder->setTotal($cart['total']);

                    $deliveries = $cart['deliveries'];
                    foreach ($cart['items'] as $shippingDate => $items) {
                        /**
                         * @var \AppBundle\Entity\StoreOrderDelivery $delivery
                         */
                        $delivery = new StoreOrderDelivery();
                        $delivery->setDeliveryDate(\DateTime::createFromFormat('Y-m-d', $shippingDate));
                        $delivery->setCost($deliveries[$shippingDate]['cost']);
                        $delivery->setDeliveryMethod($deliveries[$shippingDate]['method']);
                        $delivery->setOrderId($storeOrder->getId());
                        $delivery->setOrder($storeOrder);

                        foreach ($items as $item) {
                            /**
                             * @var \AppBundle\Entity\Product $product
                             */
                            $product = $item['product'];
                            /**
                             * @var \AppBundle\Entity\Category $category
                             */
                            $category = $em->getRepository(Category::class)->find($product->getCategoryId());
                            /**
                             * @var \AppBundle\Entity\StoreOrderProduct $orderProduct
                             */
                            $orderProduct = new StoreOrderProduct();
                            $orderProduct->setName($product->getName());
                            $orderProduct->setIsTaxable($product->getIsTaxable());
                            $orderProduct->setOldPrice($product->getOldPrice());
                            $orderProduct->setPrice($product->getPrice());
                            $orderProduct->setQty($product->getQty());
                            $orderProduct->setTotal($orderProduct->getQty() * $orderProduct->getPrice());
                            $orderProduct->setDimension($product->getDimension());
                            $orderProduct->setAvailability($product->getAvailability());
                            $orderProduct->setAvailabilityDays($product->getAvailabilityDays());
                            $orderProduct->setDescription($product->getDescription());
                            $orderProduct->setPhoto($product->getPhoto());
                            $orderProduct->setDepartmentName($category->getDepartment()->getName());
                            $orderProduct->setCategoryName($category->getName());
                            $orderProduct->setDepartmentId($category->getDepartment()->getId());
                            $orderProduct->setCategoryId($category->getId());
                            $orderProduct->setDeliveryId($delivery->getId());
                            $orderProduct->setDelivery($delivery);
                            $delivery->addProduct($orderProduct);
                        }

                        $storeOrder->addDelivery($delivery);
                    }
                }

                // TODO: Process real payment
                $params = [

                ];
                $pgwResponse = $this->processPayment($params);

                if (empty($pgwResponse)) {
                    $this->addFlash('alert', 'Error processing payment - no response from payment gateway.');
                } else {

                    // Get values
                    $Net1ApprovalIndicator = (isset($pgwResponse[1])) ? $pgwResponse[1] : ''; //A is approved E is declined/error.
                    $Net1Message = (isset($pgwResponse)) ? trim(substr($pgwResponse, 8, 32)) : '';

                    if ('A' == $Net1ApprovalIndicator) {
                        $storeOrder->setOrderStatus(1);
                        $storeOrder->setPgwResponse($pgwResponse);
                        $em->persist($storeOrder);
                        $em->flush();

                        $storeOrder->setOrderNum(sprintf('%010s', ($storeOrder->getId() + 1400)));
                        $em->persist($storeOrder);
                        $em->flush();

                        // clean session cart
                        $session = $request->getSession();
                        $session->set('cart', []);

                        // TODO: Send confirmation e-mail


                        return $this->render('payment/done.html.twig', [
                            'cart' => $this->get('app.common')->getSessionCart($request),
                            'departmentsInMenu' => $this->get('app.common')->getDepartmentsInMenu($em),
                            'storeOrder' => $storeOrder
                        ]);

                    } else {
                        $this->addFlash('alert', 'We are sorry but your credit card was declined.');
                        $this->addFlash('alert', 'Payment server responded: ' . $Net1Message . '.');
                        $this->addFlash('alert', 'Please review your credit card data or try with a different credit card and resubmit your payment.');
                    }
                }
            }
        }

        return $this->render('payment/form.html.twig', [
            'cart' => $cart,
            'departmentsInMenu' => $this->get('app.common')->getDepartmentsInMenu($em),
            'uploadDirLarge' => Photo::getUploadDirLarge(),
            'form' => $form->createView()
        ]);
    }

    /**
     * @param int $creditCardMonth
     * @param int $creditCardYear
     * @return bool
     */
    private function isCardExpired($creditCardMonth = 0, $creditCardYear = 0)
    {
        $creditCardExpDate = mktime(0, 0, 0, (int)$creditCardMonth, 1, (int)$creditCardYear);
        return ($creditCardExpDate < time());
    }

    /**
     * Uses the Luhn algorithm (aka Mod10) <http://en.wikipedia.org/wiki/Luhn_algorithm>
     * to perform basic validation of a credit card number.
     *
     * @param string $creditCardNumber The card number to validate.
     * @return boolean True if valid according to the Luhn algorith, false otherwise.
     */
    function isCardNumberValid($creditCardNumber)
    {
        $creditCardNumber = strrev($this->cardNumberClean($creditCardNumber));
        // $creditCardNumber = strrev($creditCardNumber);
        $sum = 0;

        for ($i = 0; $i < strlen($creditCardNumber); $i++) {
            $digit = (int)substr($creditCardNumber, $i, 1);

            // Double every second digit
            if ($i % 2 == 1) {
                $digit *= 2;
            }

            // Add digits of 2-digit numbers together
            if ($digit > 9) {
                $digit = ($digit % 10) + floor($digit / 10);
            }

            $sum += $digit;
        }

        // If the total has no remainder it's OK
        return ($sum % 10 == 0);
    }

    /**
     * Strips all non-numerics from the card number.
     *
     * @param string $number The card number to clean up.
     * @return string The stripped down card number.
     */
    function cardNumberClean($number)
    {
        return preg_replace("/[^0-9]/", "", $number);
    }

    /**
     * @param array $params
     * @return string
     */
    private function processPayment(array $params)
    {
        return 'A03483ZAPPROVED 03483Z                 11PZ00B54BDqGlX038260';
    }
}
