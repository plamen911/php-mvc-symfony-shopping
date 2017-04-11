<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Department;
use AppBundle\Entity\Photo;
use AppBundle\Entity\Product;
use AppBundle\Form\AddProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Home controller.
 * @package AppBundle\Controller
 * @author Plamen Markov <plamen@lynxlake.org>
 *
 * @Route("store")
 */
class StoreController extends Controller
{
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

        // $form = $this->createForm(AddProductType::class, null, ['data' => $product]);

        /**
         * @var \Symfony\Component\Form\FormBuilder $form
         */
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('store_product_add', ['id' => $product->getId()]))
            ->setMethod('POST')
            ->add('qty', NumberType::class)
            ->add('shippingDate', ChoiceType::class, [
                    'choices' => $this->getShippingDates($product),
                    'required' => false,
                    'placeholder' => 'Choose an option',
                    'empty_data' => null
                ]
            )
            ->getForm();

        return $this->render('product/view.html.twig', [
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
     */
    public function productAddAction(Product $product, Request $request)
    {
        if ($product == null) {
            throw $this->createNotFoundException('Product not found');
        }

        $shippingDate = $request->get('shippingDate', '');
        if (empty($shippingDate)) {
            // todo: error
        }

        $qty = (int)$request->get('qty', 0);
        if (empty($qty)) {
            // todo: error
        }

        // http://stackoverflow.com/questions/25956185/how-to-manage-a-simple-cart-session-in-symfony-2
        $session = $request->getSession();
        /**
         * @var Product[] $cart
         */
        $cart = $session->get('cart', []);
        for ($i = 0; $i < $qty; $i++) {
            $cart[] = [
                'product' => $product,
                'shippingDate' => $shippingDate,
            ];
        }
        $session->set('cart', $cart);

        // return $this->redirectToRoute(
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
}
