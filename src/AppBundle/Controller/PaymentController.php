<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Photo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PaymentController
 * @package AppBundle\Controller
 * @author Plamen Markov <plamen@lynxlake.org>
 *
 * @Route("payment")
 */
class PaymentController extends Controller
{
    /**
     * Display payment form.
     *
     * @Route("/", name="payment_index")
     * @Method("GET")
     *
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $cart = $this->get('app.common')->getSessionCart($request);
        if ($cart['qty'] == 0) {
            return $this->redirectToRoute('store_index');
        }

        if ($this->getUser() == null) {
            return $this->redirectToRoute('checkout_authorize');
        }



        // http://localhost:3000/payment/
        /**
         * @var \Doctrine\Common\Persistence\ObjectManager $em
         */
        $em = $this->getDoctrine()->getManager();

        // 'cart' => $this->get('app.common')->getSessionCart($request),
        // 'departmentsInMenu' => $this->get('app.common')->getDepartmentsInMenu($em)


        return $this->render('payment/form.html.twig', [
            'cart' => $cart,
            'departmentsInMenu' => $this->get('app.common')->getDepartmentsInMenu($em),
            'uploadDirLarge' => Photo::getUploadDirLarge()
        ]);
    }
}
