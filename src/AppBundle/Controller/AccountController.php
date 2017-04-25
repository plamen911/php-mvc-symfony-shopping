<?php

namespace AppBundle\Controller;

use AppBundle\Entity\StoreOrder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AccountController
 * @package AppBundle\Controller
 * @author Plamen Markov <plamen@lynxlake.org>
 *
 * @Route("account")
 */
class AccountController extends Controller
{
    /**
     * Display user orders.
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/orders", name="account_orders")
     * @Method("GET")
     *
     * @param Request $request
     * @return Response
     */
    public function ordersAction(Request $request)
    {
        /**
         * @var \Doctrine\Common\Persistence\ObjectManager $em
         */
        $em = $this->getDoctrine()->getManager();
        /**
         * @var \AppBundle\Entity\StoreOrder $storeOrders[]
         */
        $storeOrders = $em->getRepository(StoreOrder::class)
            ->findBy(['userId' => $this->getUser()->getId()], ['orderDate' => 'DESC']);

        return $this->render('account/orders.html.twig', [
            'orders' => $storeOrders
        ]);
    }

    /**
     * Display user profile form.
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/", name="account_profile")
     * @Method("GET")
     *
     * @param Request $request
     * @return Response
     */
    public function profileAction(Request $request)
    {

        return $this->render('account/profile.html.twig', [

        ]);

    }


}
