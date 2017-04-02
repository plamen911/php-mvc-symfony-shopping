<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Department;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Department controller.
 * @package AppBundle\Controller
 * @author Plamen Markov <plamen@lynxlake.org>
 *
 * @Route("admin/departments")
 */
class DepartmentController extends Controller
{
    /**
     * Lists all department entities.
     *
     * @Route("/", name="department_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $departments = $em->getRepository('AppBundle:Department')->findBy([], ['position' => 'ASC']);

        return $this->render('admin/department/index.html.twig', array(
            'departments' => $departments,
        ));
    }

    /**
     * Creates a new department entity.
     *
     * @Route("/new", name="department_new")
     * @Method({"POST"})
     *
     * @return JsonResponse
     */
    public function newAction()
    {
        $department = new Department();
        $department->setName('Unspecified');
        $department->setShowInMenu(false);
        $department->setPosition($this->getMaxPosition() + 1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($department);
        $em->flush();

        return new JsonResponse([
            'code' => 0,
            'message' => 'ok',
            'data' => ['id' => $department->getId(), 'name' => $department->getName()]
        ]);
    }

    /**
     * Displays a form to edit an existing department entity.
     *
     * @Route("/{id}/edit", name="department_edit", requirements={"id": "\d+"})
     * @Method({"POST"})
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function editAction($id = 0, Request $request)
    {
        /**
         * @var \AppBundle\Entity\Department $department
         */
        $department = $this->getDoctrine()->getRepository(Department::class)->find($id);

        if ($department === null) {
            return new JsonResponse([
                'code' => 1,
                'message' => 'Department not found',
                'data' => []
            ]);
        }

        $department->setName($request->get('name'));
        $department->setShowInMenu((bool)$request->get('show_in_menu'));

        $em = $this->getDoctrine()->getManager();
        $em->persist($department);
        $em->flush();

        return new JsonResponse([
            'code' => 0,
            'message' => 'ok',
            'data' => ['id' => $department->getId()]
        ]);
    }

    /**
     * Sort departments with drag and drop.
     *
     * @Route("/sort", name="department_sort")
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
                 * @var \AppBundle\Entity\Department $department
                 */
                $department = $this->getDoctrine()->getRepository(Department::class)->find($item_id);
                if ($department) {
                    $department->setPosition($i + 1);
                    $em->persist($department);
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
     * Deletes a department entity.
     *
     * @Route("/{id}", name="department_delete", requirements={"id": "\d+"})
     * @Method("DELETE")
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deleteAction($id = 0)
    {
        /**
         * @var \AppBundle\Entity\Department $department
         */
        $department = $this->getDoctrine()->getRepository(Department::class)->find((int)$id);
        if ($department === null) {
            return new JsonResponse([
                'code' => 1,
                'message' => 'Department not found',
                'data' => []
            ]);
        }

        $em = $this->getDoctrine()->getManager();
        /**
         * @var \AppBundle\Entity\Department $department
         */
        foreach ($department->getCategories() as $category) {
            /**
             * @var \AppBundle\Entity\Product $product
             */
            foreach ($category->getProducts() as $product) {
                /**
                 * @var \AppBundle\Entity\Photo $photo
                 */
                foreach ($product->getPhotos() as $photo) {
                    $product->removePhoto($photo);
                }
                $em->remove($product);
            }
            $em->remove($category);
        }
        $em->remove($department);
        $em->flush();

        return new JsonResponse([
            'code' => 0,
            'message' => 'ok',
            'data' => []
        ]);
    }

    /**
     * Creates a form to delete a department entity.
     *
     * @param Department $department The department entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Department $department)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('department_delete', array('id' => $department->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    private function getMaxPosition() {
        $em = $this->getDoctrine()->getManager();
        $department = $em->getRepository('AppBundle:Department')->findOneBy([], ['position' => 'DESC']);

        /**
         * @var \AppBundle\Entity\Department $department
         */
        return ($department) ? $department->getPosition() : 0;
    }
}
