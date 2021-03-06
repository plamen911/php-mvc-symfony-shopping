<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Department;
use AppBundle\Entity\Photo;
use AppBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints;

/**
 * Home controller.
 * @package AppBundle\Controller
 * @author Plamen Markov <plamen@lynxlake.org>
 *
 * @Route("store")
 */
class StoreController extends Controller
{
    private $stateTax = 7;
    private $deliveryCost = 35;

    /**
     * @Route("/", name="store_index")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $data = $this->getSearchResults($request->get('keyword', 'today'));
        $data['cart'] = $this->get('app.common')->getSessionCart($request);

        return $this->render('search/index.html.twig', $data);
    }

    /**
     * @Route("/search", name="store_search_by_keyword")
     * @Method({"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        $data = $this->getSearchResults($request->get('keyword', ''));
        $data['cart'] = $this->get('app.common')->getSessionCart($request);

        return $this->render('search/index.html.twig', $data);
    }

    /**
     * @Route("/search/department/{id}", name="store_search_by_department", requirements={"id":"\d+"})
     * @Method({"GET"})
     *
     * @param Department $department
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchByDepartment(Department $department, Request $request)
    {
        if ($department == null) {
            throw $this->createNotFoundException('Department not found');
        }

        $data = $this->getSearchResults('department', $department->getId());
        $data['keyword'] = '';
        $data['cart'] = $this->get('app.common')->getSessionCart($request);

        return $this->render('search/index.html.twig', $data);
    }

    /**
     * @Route("/search/category/{id}", name="store_search_by_category", requirements={"id":"\d+"})
     * @Method({"GET"})
     *
     * @param Category $category
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchByCategory(Category $category, Request $request)
    {
        if ($category == null) {
            throw $this->createNotFoundException('Category not found');
        }

        $data = $this->getSearchResults('category', $category->getId());
        $data['keyword'] = '';
        $data['cart'] = $this->get('app.common')->getSessionCart($request);

        return $this->render('search/index.html.twig', $data);
    }

    /**
     * @Route("/product/{id}", name="store_product", requirements={"id":"\d+"})
     * @Method({"GET"})
     *
     * @param Product $product
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function productAction(Product $product, Request $request)
    {
        if ($product == null) {
            throw $this->createNotFoundException('Product not found');
        }

        if ((float)$product->getQty() <= 0) {
            throw $this->createNotFoundException('Product is currently unavailable for shopping');
        }

        $form = $this->createProductForm($product);

        /**
         * @var \Doctrine\Common\Persistence\ObjectManager $em
         */
        $em = $this->getDoctrine()->getManager();

        return $this->render('product/view.html.twig', [
            'cart' => $this->get('app.common')->getSessionCart($request),
            'departmentsInMenu' => $this->get('app.common')->getDepartmentsInMenu($em),
            'error' => '',
            'product' => $product,
            'form' => $form->createView(),
            'uploadDirLarge' => Photo::getUploadDirLarge()
        ]);
    }

    /**
     * @Route("/product/{id}", name="store_product_add", requirements={"id":"\d+"})
     * @Method({"POST"})
     *
     * @param Product $product
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function productAddAction(Product $product, Request $request)
    {
        if ($product == null) {
            throw $this->createNotFoundException('Product not found');
        }

        $form = $this->createProductForm($product);
        $form->handleRequest($request);

//        dump($form->getErrors(true)->getChildren()->getMessage());
//        dump($form->getErrors(true)->count());

        $data = $form->getData();
        $error = [];
        if (empty($data['qty']) || 1 > (int)$data['qty']) {
            $error[] = 'Product quantity is missing.';
        }
        if (empty($data['shippingDate']) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['shippingDate'])) {
            $error[] = 'Delivery date is missing.';
        }

        if (empty($error) && $form->isSubmitted() && $form->isValid()) {
            $this->addProductToCart($request, $product, $data['qty'], $data['shippingDate']);

            $this->addFlash('success', 'Product successfully added to card.');
            return $this->redirectToRoute('store_cart');
        }

        /**
         * @var \Doctrine\Common\Persistence\ObjectManager $em
         */
        $em = $this->getDoctrine()->getManager();

        return $this->render('product/view.html.twig', [
            'cart' => $this->get('app.common')->getSessionCart($request),
            'departmentsInMenu' => $this->get('app.common')->getDepartmentsInMenu($em),
            'error' => (!empty($error)) ? implode('<br>', $error) : '',
            'product' => $product,
            'form' => $form->createView(),
            'uploadDirLarge' => Photo::getUploadDirLarge()
        ]);
    }

    /**
     * @Route("/cart", name="store_cart")
     * @Method({"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cartAction(Request $request)
    {
        // $session->clear();
        $cart = $this->get('app.common')->getSessionCart($request);

        $form = $this->createShoppingCartForm();

        /**
         * @var \Doctrine\Common\Persistence\ObjectManager $em
         */
        $em = $this->getDoctrine()->getManager();

        return $this->render('store/cart.html.twig', [
            'cart' => $cart,
            'departmentsInMenu' => $this->get('app.common')->getDepartmentsInMenu($em),
            'deliveryCost' => $this->deliveryCost,
            'form' => $form->createView(),
            'uploadDirLarge' => Photo::getUploadDirLarge()

        ]);
    }

    /**
     * @Route("/cart", name="store_cart_update")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function cartUpdateAction(Request $request)
    {
        $form = $this->createShoppingCartForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('update')->isClicked()) {
                $this->cartUpdate($request);
                $this->addFlash('success', 'Your cart was successfully updated.');
                return $this->redirectToRoute('store_cart');

            } elseif ($form->get('checkout')->isClicked()) {
                return $this->redirectToRoute('checkout_authorize');
            }
        }

        return $this->cartAction($request);
    }

    /**
     * @Route("/cart/remove/{shippingDate}", name="store_cart_remove", requirements={"$shippingDate":"\d{4}-\d{2}-\d{2}"})
     * @Method({"GET"})
     *
     * @param string $shippingDate
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function removeFromCartAction($shippingDate = '', Request $request)
    {
        $this->removeFromCart($shippingDate, $request);
        $this->addFlash('success', 'Delivery successfully removed from your cart.');
        return $this->redirectToRoute('store_cart');
    }

    /**
     * @param Product $product
     * @param float $qty
     * @return string
     */
    private function getProductTaxes($product, $qty)
    {
        $taxes = 0;
        /**
         * @var Product $product
         */
        if ($product->getIsTaxable()) {
            $taxPercent = $this->stateTax;
            $taxes = ($qty * $taxPercent) * $taxPercent / 100;
        }

        return sprintf('%.2f', $taxes);
    }

    /**
     * Creates a form to update product entities in shopping cart.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createShoppingCartForm()
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('store_cart'))
            ->setMethod('POST')
            ->add('update', SubmitType::class, ['label' => 'Update cart', 'attr' => ['class' => 'button', 'required' => false]])
            ->add('checkout', SubmitType::class, ['label' => 'Check out', 'attr' => ['class' => 'button', 'required' => false]])
            ->getForm();
    }

    /**
     * Creates a form to add a product entity to cart.
     *
     * @param Product $product The product entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createProductForm(Product $product)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('store_product_add', ['id' => $product->getId()]))
            ->setMethod('POST')
            ->add('qty', IntegerType::class, [
                    'empty_data' => 1,
                    'required' => true,
                    'data' => 1,
//                    'constraints' => [
//                        new Constraints\GreaterThan(['value' => 0, 'message' => 'Please enter product quantity.'])
//                    ]
                ]
            )
            ->add('shippingDate', ChoiceType::class, [
                    'choices' => $this->getShippingDates($product),
                    'required' => true,
                    'placeholder' => 'Choose an option',
                    'empty_data' => null,
//                    'constraints' => [
//                        new Constraints\NotBlank(['message' => 'Please select delivery date.'])
//                    ]
                ]
            )
            ->getForm();
    }

    /**
     * @param Product $product
     * @return array
     */
    private function getShippingDates(Product $product)
    {
        // If time is after 10:00 AM and product quantity is less or equal 0 - remove 1st option in Delivery Date dropdown
        $removeFirstOption = (date('G') > 10 || $product->getQty() <= 0) ? true : false;

        if ('weekdays' == $product->getAvailability() && $product->getAvailabilityDays() != '') {
            $weekDays = explode(',', $product->getAvailabilityDays());

            $todayTs = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $oneDaysTs = 60 * 60 * 24;

            $ts = $todayTs;
            $tmp = [];

            for ($i = 0; $i < 1000; $i++) {
                if (count($tmp) > 10) {
                    break;
                }
                if (in_array(date('l', $ts), $weekDays)) {
                    $key = date('Y-m-d', $ts);
                    $val = date('l - M jS', $ts);
                    $tmp[$key] = $val;
                }
                $ts += $oneDaysTs;
            }

            return array_flip($tmp);

        } elseif ('daily' == $product->getAvailability()) {
            $mm = date('n');
            $dd = date('j');
            $yy = date('Y');
            $tmp = [];

            for (; $dd < date('j') + 30; $dd++) {
                $ts = mktime(0, 0, 0, $mm, $dd, $yy);

                $key = date('Y-m-d', $ts);
                $val = date('l - M jS', $ts);
                $tmp[$key] = $val;
            }

            $j = 0;
            foreach ($tmp as $key => $val) {
                if (!$j && $removeFirstOption) {
                    unset($tmp[$key]);
                    break;
                }
                $j++;
            }

            ksort($tmp);
            return array_flip($tmp);

        } else if ($product->getAvailability() == 'multidays' && $product->getAvailabilityDays() != '') {

            $dates = explode(',', $product->getAvailabilityDays());

            $tmp = [];
            $todayTs = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            foreach ($dates as $date) {
                $ts = strtotime($date);
                if ($ts < $todayTs) {
                    continue;
                }

                $key = date('Y-m-d', $ts);
                $val = date('l - M jS', $ts);
                $tmp[$key] = $val;
            }

            $j = 0;
            foreach ($tmp as $key => $val) {
                if (!$j && $removeFirstOption) {
                    unset($tmp[$key]);
                    break;
                }
                $j++;
            }

            ksort($tmp);
            return array_flip($tmp);
        }

        return [];
    }

    /**
     * @param string $term
     * @param string $value
     * @return array
     */
    private function getSearchResults($term = '', $value = '')
    {
        /**
         * @var \Doctrine\Common\Persistence\ObjectManager $em
         */
        $em = $this->getDoctrine()->getManager();

        $data = $em->getRepository(Product::class)
            ->findAllByKeyword($term, $value);

        return [
            'departmentsInMenu' => $this->get('app.common')->getDepartmentsInMenu($em),
            'items' => $data->items,
            'departments' => $data->departments,
            'categories' => $data->categories,
            'keyword' => $data->keyword,
            'uploadDirLarge' => Photo::getUploadDirLarge()
        ];
    }

    /**
     * @param Request $request
     * @param Product $product
     * @param int $qty
     * @param string $shippingDate
     */
    private function addProductToCart(Request $request, Product $product, $qty = 0, $shippingDate = '0000-00-00')
    {
        $cart = $this->get('app.common')->getSessionCart($request);

        if (!isset($cart['items'])) {
            $cart['items'] = [];
            $cart['deliveries'] = [];
        }
        if (!isset($cart['items'][$shippingDate])) {
            $cart['items'][$shippingDate] = [];
            $cart['deliveries'][$shippingDate] = [
                'method' => 'Delivery',
                'cost' => $this->deliveryCost
            ];
        }

        $item = [];
        if (isset($cart['items'][$shippingDate][$product->getId()])) {
            $item = $cart['items'][$shippingDate][$product->getId()];
        } else {
            $item['product'] = $product;
            $item['qty'] = 0;
        }
        $item['qty'] += (int)$qty;
        $item['taxes'] = (float)$this->getProductTaxes($product, $item['qty']);
        $item['shippingDate'] = $shippingDate;

        $cart['items'][$shippingDate][$product->getId()] = $item;

        $cart = $this->calculateCartTotals($cart);

        ksort($cart['items']);

        $session = $request->getSession();
        $session->set('cart', $cart);
    }

    /**
     * @param array $cart
     * @return array
     */
    private function calculateCartTotals(array $cart) {
        $cart['qty'] = 0;
        $cart['subtotal'] = 0;
        $cart['taxes'] = 0;
        $cart['delivery'] = 0;
        $cart['total'] = 0;

        // Calculate cart totals
        if (!empty($cart['items'])) {
            foreach ($cart['items'] as $shippingDate => $items) {
                foreach ($items as $item) {
                    /**
                     * @var Product $product
                     */
                    $product = $item['product'];
                    $cart['qty'] += $item['qty'];
                    $cart['subtotal'] += $item['qty'] * (float)$product->getPrice();
                    $cart['taxes'] += (float)$item['taxes'];
                    $cart['delivery'] += (float)$cart['deliveries'][$shippingDate]['cost'];
                    $cart['total'] += $cart['subtotal'] + $cart['taxes'] + $cart['delivery'];
                }
            }
        }

        return $cart;
    }

    /**
     * @param Request $request
     */
    private function cartUpdate(Request $request)
    {
        $cart = $this->get('app.common')->getSessionCart($request);

        foreach ($request->request->all() as $key => $item) {
            if (preg_match('/^delivery-method-(\d{4}-\d{2}-\d{2})$/', $key, $matches)) {
                $shippingDate = $matches[1];
                $method = $item;
                if (isset($cart['deliveries'][$shippingDate])) {
                    $cart['deliveries'][$shippingDate] = [
                        'method' => $method,
                        'cost' => ('Delivery' === $method) ? $this->deliveryCost : 0
                    ];
                }
            }

            if (preg_match('/^qty-(\d{4}-\d{2}-\d{2})-(\d+)$/', $key, $matches)) {
                $shippingDate = $matches[1];
                $productId = $matches[2];
                $qty = (int)$item;
                if (isset($cart['items'][$shippingDate][$productId])) {
                    if (0 >= $qty) {
                        unset($cart['items'][$shippingDate][$productId]);
                    } else {
                        $item = $cart['items'][$shippingDate][$productId];
                        $item['qty'] = $qty;
                        $item['taxes'] = (float)$this->getProductTaxes($item['product'], $item['qty']);
                        $item['shippingDate'] = $shippingDate;
                        $cart['items'][$shippingDate][$productId] = $item;
                    }
                }
            }
        }
        $cart = $this->calculateCartTotals($cart);

        $session = $request->getSession();
        $session->set('cart', $cart);
    }

    /**
     * @param string $shippingDate
     * @param Request $request
     */
    private function removeFromCart($shippingDate = '', Request $request)
    {
        $cart = $this->get('app.common')->getSessionCart($request);;

        if (isset($cart['deliveries'][$shippingDate])) {
            unset($cart['deliveries'][$shippingDate]);
        }

        if (isset($cart['items'][$shippingDate])) {
            unset($cart['items'][$shippingDate]);
        }

        $cart = $this->calculateCartTotals($cart);

        $session = $request->getSession();
        $session->set('cart', $cart);
    }
}
