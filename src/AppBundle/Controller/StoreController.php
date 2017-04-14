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
    private const STATE_TAX = 7;
    private const DELIVERY_COST = 35;

    /**
     * @Route("/", name="store_index")
     */
    public function indexAction()
    {
        return $this->render('store/index.html.twig', [
            'departmentsInMenu' => $this->getDepartmentsInMenu()
        ]);
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
        return $this->render('search/index.html.twig',
            $this->getSearchResults($request->get('keyword', ''))
        );
    }

    /**
     * @Route("/search/department/{id}", name="store_search_by_department", requirements={"id":"\d+"})
     * @Method({"GET"})
     *
     * @param Department $department
     * @return \Symfony\Component\HttpFoundation\Response
     * @internal param Request $request
     */
    public function searchByDepartment(Department $department)
    {
        if ($department == null) {
            throw $this->createNotFoundException('Department not found');
        }

        $data = $this->getSearchResults('department', $department->getId());
        $data['keyword'] = '';

        return $this->render('search/index.html.twig', $data);
    }

    /**
     * @Route("/search/category/{id}", name="store_search_by_category", requirements={"id":"\d+"})
     * @Method({"GET"})
     *
     * @param Category $category
     * @return \Symfony\Component\HttpFoundation\Response
     * @internal param Request $request
     */
    public function searchByCategory(Category $category)
    {
        if ($category == null) {
            throw $this->createNotFoundException('Category not found');
        }

        $data = $this->getSearchResults('category', $category->getId());
        $data['keyword'] = '';

        return $this->render('search/index.html.twig', $data);
    }

    /**
     * @Route("/product/{id}", name="store_product", requirements={"id":"\d+"})
     * @Method({"GET"})
     *
     * @param Product $product
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function productAction(Product $product)
    {
        if ($product == null) {
            throw $this->createNotFoundException('Product not found');
        }

        if ((float)$product->getQty() <= 0) {
            throw $this->createNotFoundException('Product is currently unavailable for shopping');
        }

        $form = $this->createProductForm($product);

        return $this->render('product/view.html.twig', [
            'error' => '',
            'product' => $product,
            'form' => $form->createView(),
            'uploadDirLarge' => Photo::getUploadDirLarge(),
            'departmentsInMenu' => $this->getDepartmentsInMenu()
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
            $session = $request->getSession();
            $this->addProductToCart($session, $product, $data['qty'], $data['shippingDate']);

            $this->addFlash('success', 'Product successfully added to card.');
            return $this->redirectToRoute('store_cart');
        }

        return $this->render('product/view.html.twig', [
            'error' => (!empty($error)) ? implode('<br>', $error) : '',
            'product' => $product,
            'form' => $form->createView(),
            'uploadDirLarge' => Photo::getUploadDirLarge(),
            'departmentsInMenu' => $this->getDepartmentsInMenu()
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
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        $form = $this->createShoppingCartForm();

        return $this->render('store/cart.html.twig', [
            'cart' => $cart,
            'deliveryCost' => self::DELIVERY_COST,
            'form' => $form->createView(),
            'uploadDirLarge' => Photo::getUploadDirLarge(),
            'departmentsInMenu' => $this->getDepartmentsInMenu()
        ]);
    }

    /**
     * @Route("/cart", name="store_cart_update")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cartUpdateAction(Request $request)
    {
        $form = $this->createShoppingCartForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('update')->isClicked()) {

                $this->cartUpdate($request);
                // return $this->redirectToRoute('store_cart');

            } elseif ($form->get('checkout')->isClicked()) {
                // todo - redirect to checkout
            }
        }

        dump('update NOT clicked...');

        return $this->cartAction($request);
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
            $taxPercent = self::STATE_TAX;
            $taxes = ($qty * $taxPercent) * $taxPercent / 100;
        }

        return sprintf('%.2f', $taxes);
    }

    /**
     * Creates a form to update product entities in shopping cart.
     *
     * @param Product $product The product entity
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
                    'empty_data' => 0,
                    'required' => true,
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
         * @var \Doctrine\ORM\EntityManager $em
         */
        $em = $this->getDoctrine()->getManager();

        $data = $em->getRepository(Product::class)
            ->findAllByKeyword($term, $value);

        return [
            'departmentsInMenu' => $this->getDepartmentsInMenu(),
            'items' => $data->items,
            'departments' => $data->departments,
            'categories' => $data->categories,
            'keyword' => $data->keyword,
            'uploadDirLarge' => Photo::getUploadDirLarge()
        ];
    }

    /**
     * @return Department[]
     */
    private function getDepartmentsInMenu()
    {
        return $this->getDoctrine()->getManager()->getRepository(Department::class)
            ->findBy(['showInMenu' => true], ['position' => 'ASC']);
    }

    /**
     * @param SessionInterface $session
     * @param Product $product
     * @param int $qty
     * @param string $shippingDate
     */
    private function addProductToCart(SessionInterface $session, Product $product, $qty = 0, $shippingDate = '0000-00-00')
    {
        $cart = $session->get('cart', []);
        if (!isset($cart['items'])) {
            $cart['items'] = [];
            $cart['deliveries'] = [];
        }
        if (!isset($cart['items'][$shippingDate])) {
            $cart['items'][$shippingDate] = [];
            $cart['deliveries'][$shippingDate] = [
                'method' => 'Delivery',
                'cost' => self::DELIVERY_COST
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
        foreach ($cart['items'] as $shippingDate => $items) {
            foreach ($items as $item) {
                /**
                 * @var Product $product
                 */
                $product = $item['product'];

                // dump($item['product']);

                $cart['qty'] += $item['qty'];
                $cart['subtotal'] += $item['qty'] * (float)$product->getPrice();
                $cart['taxes'] += (float)$item['taxes'];
                $cart['delivery'] += (float)$cart['deliveries'][$shippingDate]['cost'];
                $cart['total'] += $cart['subtotal'] + $cart['taxes'] + $cart['delivery'];
            }
        }

        dump($cart);

        return $cart;
    }

    /**
     * @param Request $request
     */
    private function cartUpdate(Request $request)
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        foreach ($request->request->all() as $key => $item) {
            if (preg_match('/^delivery-method-(\d{4}-\d{2}-\d{2})$/', $key, $matches)) {
                $shippingDate = $matches[1];
                $method = $item;
                if (isset($cart['deliveries'][$shippingDate])) {
                    $cart['deliveries'][$shippingDate] = [
                        'method' => $method,
                        'cost' => ('Delivery' === $method) ? self::DELIVERY_COST : 0
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

        $session->set('cart', $cart);
    }
}
