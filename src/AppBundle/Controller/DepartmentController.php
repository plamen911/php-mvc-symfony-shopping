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
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
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
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $department = new Department();
        $form = $this->createForm('AppBundle\Form\DepartmentType', $department);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($department);
            $em->flush();

            return $this->redirectToRoute('department_show', array('id' => $department->getId()));
        }

        return $this->render('admin/department/new.html.twig', array(
            'department' => $department,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a department entity.
     *
     * @Route("/{id}", name="department_show")
     * @Method("GET")
     */
    public function showAction(Department $department)
    {
        $deleteForm = $this->createDeleteForm($department);

        return $this->render('admin/department/show.html.twig', array(
            'department' => $department,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing department entity.
     *
     * @Route("/{id}/edit", name="department_edit")
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

        $em = $this->getDoctrine()->getManager();

        $department->setName($request->get('name'));
        $department->setShowInMenu((bool)$request->get('show_in_menu'));

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
     * @Route("/{id}", name="department_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Department $department)
    {
        $form = $this->createDeleteForm($department);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($department);
            $em->flush($department);
        }

        return $this->redirectToRoute('department_index');
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
}
