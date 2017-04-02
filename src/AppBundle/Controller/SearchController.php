<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Search controller
 * @package AppBundle\Controller
 * @author Plamen Markov <plamen@lynxlake.org>
 */
class SearchController extends Controller
{
    /**
     * @Route("admin/search", name="product_search")
     * @Method({"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $keyword = $request->get('keyword', '');

        $query = $this->getDoctrine()
            ->getRepository(Product::class)
            ->createQueryBuilder('product')
            ->leftJoin('product.category', 'category')
            ->leftJoin('category.department', 'department')
            ->where('product.name LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->setMaxResults(100)
            ->orderBy('department.name', 'ASC')
            ->addOrderBy('category.name', 'ASC')
            ->addOrderBy('product.name', 'ASC')
            ->getQuery();

        $items = [];
        $departments = [];
        $categories = [];

        $products = $query->getResult();
        /**
         * @var \AppBundle\Entity\Product $product
         */
        foreach ($products as $product) {
            $departmentId = $product->getCategory()->getDepartment()->getId();
            $categoryId = $product->getCategory()->getId();
            $items[$departmentId][$categoryId][$product->getId()] = $product;

            $departments[$departmentId] = $product->getCategory()->getDepartment();
            $categories[$categoryId] = $product->getCategory();
        }

        return $this->render('search/index.html.twig', [
            'items' => $items,
            'departments' => $departments,
            'categories' => $categories,
            'keyword' => $keyword,
        ]);
    }
}
