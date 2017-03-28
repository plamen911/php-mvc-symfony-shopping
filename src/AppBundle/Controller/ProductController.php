<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Product;
use AppBundle\Entity\Department;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Product controller.
 *
 * @Route("admin/department/{departmentId}/category/{categoryId}/products")
 */
class ProductController extends Controller
{
    /**
     * Lists all category entities.
     *
     * @Route("/", name="product_index")
     * @Method("GET")
     *
     * @param int $departmentId
     * @param int $categoryId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($departmentId = 0, $categoryId = 0)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find((int)$categoryId);
        if ($category === null) {
            throw $this->createNotFoundException('Category does not exist!');
        }

        $department = $this->getDoctrine()->getRepository(Department::class)->find((int)$departmentId);
        if ($department === null) {
            throw $this->createNotFoundException('Department does not exist!');
        }

        if ($category->getDepartmentId() != $department->getId()) {
            throw $this->createNotFoundException('It seems that the category does not belong to department!');
        }

        $em = $this->getDoctrine()->getManager();
        $products = $em->getRepository('AppBundle:Product')->findBy(['categoryId' => $category->getId()], ['position' => 'ASC']);

        return $this->render('admin/product/index.html.twig', array(
            'department' => $department,
            'category' => $category,
            'products' => $products,
        ));
    }

    /**
     * Creates a new product entity.
     *
     * @Route("/new", name="product_new")
     * @Method({"POST"})
     *
     * @param int $departmentId
     * @param int $categoryId
     * @return JsonResponse
     */
    public function newAction($departmentId = 0, $categoryId = 0)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find((int)$categoryId);
        if ($category === null) {
            return new JsonResponse([
                'code' => 1,
                'message' => 'Category does not exist!',
                'data' => []
            ]);
        }

        if ($category->getDepartmentId() != $departmentId) {
            return new JsonResponse([
                'code' => 1,
                'message' => 'It seems that the category does not belong to department!',
                'data' => []
            ]);
        }

        $product = new Product();
        $product->setCategoryId($category->getId());
        $product->setCategory($category);
        $product->setName('Unspecified');
        $product->setPosition($this->getMaxPosition($categoryId) + 1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->flush();

        return new JsonResponse([
            'code' => 0,
            'message' => 'ok',
            'data' => ['id' => $product->getId(),
                'departmentId' => $category->getDepartmentId(),
                'categoryId' => $category->getId(),
                'name' => $product->getName()
            ]
        ]);
    }

    /**
     * Displays a form to edit an existing product entity.
     *
     * @Route("/{id}/edit", name="product_edit")
     * @Method({"GET","POST"})
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id = 0, Request $request)
    {
        /**
         * @var \AppBundle\Entity\Product $product
         */
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        if($request->isXmlHttpRequest()) {
            if ($product === null) {
                return new JsonResponse([
                    'code' => 1,
                    'message' => 'Product not found',
                    'data' => []
                ]);
            }

            $product->setName($request->get('name'));

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            return new JsonResponse([
                'code' => 0,
                'message' => 'ok',
                'data' => ['id' => $product->getId(), 'categoryId' => $product->getCategoryId()]
            ]);

        } else {



            return $this->render('admin/product/edit.html.twig', array(
                'department' => $product->getCategory()->getDepartment(),
                'category' => $product->getCategory(),
                'product' => $product,
            ));
        }
    }

    /**
     * Sort categories with drag and drop.
     *
     * @Route("/sort", name="product_sort")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sortAction(Request $request)
    {
        $sorted = $request->get('item', []);
        if (!empty($sorted)) {
            $em = $this->getDoctrine()->getManager();
            foreach ($sorted as $i => $item_id) {
                /**
                 * @var \AppBundle\Entity\Product $product
                 */
                $product = $this->getDoctrine()->getRepository(Product::class)->find($item_id);
                if ($product) {
                    $product->setPosition($i + 1);
                    $em->persist($product);
                    $em->flush();
                }
            }
        }

        return new JsonResponse([
            'code' => 0,
            'message' => 'ok',
            'data' => []
        ]);
    }

    /**
     * Deletes a product entity.
     *
     * @Route("/{id}", name="product_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Product $product)
    {
        $form = $this->createDeleteForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
        }

        return $this->redirectToRoute('product_index');
    }

    /**
     * Creates a form to delete a product entity.
     *
     * @param Product $product The product entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Product $product)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('product_delete', array('id' => $product->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    private function getMaxPosition($categoryId = 0) {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('AppBundle:Product')->findOneBy(['categoryId' => $categoryId], ['position' => 'DESC']);

        /**
         * @var \AppBundle\Entity\Product $product
         */
        return ($product) ? $product->getPosition() : 0;
    }
}
