<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Department;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Category controller.
 *
 * @Route("admin/departments/{departmentId}/categories")
 */
class CategoryController extends Controller
{
    /**
     * Lists all category entities.
     *
     * @Route("/", name="category_index")
     * @Method("GET")
     *
     * @param int $departmentId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($departmentId = 0)
    {
        $department = $this->getDoctrine()->getRepository(Department::class)->find((int)$departmentId);
        if ($department === null) {
            throw $this->createNotFoundException('Department does not exist!');
        }

        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('AppBundle:Category')->findBy(['departmentId' => $department->getId()], ['position' => 'ASC']);

        return $this->render('admin/category/index.html.twig', array(
            'department' => $department,
            'categories' => $categories,
        ));
    }

    /**
     * Creates a new category entity.
     *
     * @Route("/new", name="category_new")
     * @Method({"POST"})
     *
     * @param int $departmentId
     * @return JsonResponse
     */
    public function newAction($departmentId = 0)
    {
        $department = $this->getDoctrine()->getRepository(Department::class)->find((int)$departmentId);
        if ($department === null) {
            return new JsonResponse([
                'code' => 1,
                'message' => 'Department does not exist!',
                'data' => []
            ]);
        }

        $category = new Category();
        $category->setDepartmentId($department->getId());
        $category->setDepartment($department);
        $category->setName('Unspecified');
        $category->setPosition($this->getMaxPosition($departmentId) + 1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($category);
        $em->flush();

        return new JsonResponse([
            'code' => 0,
            'message' => 'ok',
            'data' => ['id' => $category->getId(), 'departmentId' => $department->getId(), 'name' => $category->getName()]
        ]);
    }

    /**
     * Displays a form to edit an existing category entity.
     *
     * @Route("/{id}/edit", name="category_edit")
     * @Method({"POST"})
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function editAction($id = 0, Request $request)
    {
        /**
         * @var \AppBundle\Entity\Category $category
         */
        $category = $this->getDoctrine()->getRepository(Category::class)->find($id);

        if ($category === null) {
            return new JsonResponse([
                'code' => 1,
                'message' => 'Category not found',
                'data' => []
            ]);
        }

        $category->setName($request->get('name'));
        $category->setDescription($request->get('description'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($category);
        $em->flush();

        return new JsonResponse([
            'code' => 0,
            'message' => 'ok',
            'data' => ['id' => $category->getId(), 'departmentId' => $category->getDepartmentId()]
        ]);
    }

    /**
     * Sort categories with drag and drop.
     *
     * @Route("/sort", name="category_sort")
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
                 * @var \AppBundle\Entity\Category $category
                 */
                $category = $this->getDoctrine()->getRepository(Category::class)->find($item_id);
                if ($category) {
                    $category->setPosition($i + 1);
                    $em->persist($category);
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
     * Deletes a category entity.
     *
     * @Route("/{id}", name="category_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Category $category)
    {
        $form = $this->createDeleteForm($category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($category);
            $em->flush();
        }

        return $this->redirectToRoute('category_index');
    }

    /**
     * Creates a form to delete a category entity.
     *
     * @param Category $category The category entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Category $category)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('category_delete', array('id' => $category->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    private function getMaxPosition($departmentId = 0) {
        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('AppBundle:Category')->findOneBy(['departmentId' => $departmentId], ['position' => 'DESC']);

        /**
         * @var \AppBundle\Entity\Category $category
         */
        return ($category) ? $category->getPosition() : 0;
    }
}
