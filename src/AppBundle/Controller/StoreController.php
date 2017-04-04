<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Department;
use AppBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
        /**
         * @var \Doctrine\ORM\EntityManager $em
         */
        $em = $this->getDoctrine()->getManager();

        $departments = $em->getRepository(Department::class)
            ->findBy(['showInMenu' => true], ['position' => 'ASC']);

        return $this->render('store/index.html.twig', [
            'departmentsInMenu' => $departments
        ]);
    }

    /**
     * @Route("/search", name="store_search")
     * @Method({"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        /**
         * @var \Doctrine\ORM\EntityManager $em
         */
        $em = $this->getDoctrine()->getManager();

        $departments = $em->getRepository(Department::class)
            ->findBy(['showInMenu' => true], ['position' => 'ASC']);

        $data = $em->getRepository(Product::class)
            ->findAllByKeyword($request->get('keyword', ''));

        return $this->render('search/index.html.twig', [
            'departmentsInMenu' => $departments,
            'items' => $data->items,
            'departments' => $data->departments,
            'categories' => $data->categories,
            'keyword' => $data->keyword,
        ]);
    }
}
