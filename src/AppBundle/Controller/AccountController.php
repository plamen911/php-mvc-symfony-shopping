<?php

namespace AppBundle\Controller;

use AppBundle\Entity\StoreOrder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
        $form = $this->getSearchForm();
        $form->handleRequest($request);

        /**
         * @var \Doctrine\Common\Persistence\ObjectManager $em
         */
        $em = $this->getDoctrine()->getManager();

        $params = [
            'userId' => $this->getUser()->getId(),
            'orderNum' => $form['orderNum']->getData(),
            'orderDateFrom' => $form['orderDateFrom']->getData(),
            'orderDateTo' => $form['orderDateTo']->getData(),
            'deliveryDateFrom' => $form['deliveryDateFrom']->getData(),
            'deliveryDateTo' => $form['deliveryDateTo']->getData(),
        ];

        /**
         * @var \AppBundle\Entity\StoreOrder $storeOrders[]
         */
        $storeOrders = $em->getRepository(StoreOrder::class)
            ->search($params);

        return $this->render('account/orders.html.twig', [
            'orders' => $storeOrders,
            'form' => $form->createView()
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

    /**
     * Creates a form to search orders.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function getSearchForm()
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('account_orders'))
            ->setMethod('GET')
            ->add('orderNum', TextType::class, ['label' => 'Order Num.', 'required' => false, 'attr' => ['placeholder' => 'Order #...']])
            ->add('orderDateFrom', TextType::class, ['label' => 'Order Date From', 'required' => false, 'attr' => ['placeholder' => 'Order Date From...', 'data-date-format' => 'mm/dd/yyyy']])
            ->add('orderDateTo', TextType::class, ['label' => 'Order Date To', 'required' => false, 'attr' => ['placeholder' => 'Order Date To...', 'data-date-format' => 'mm/dd/yyyy']])
            ->add('deliveryDateFrom', TextType::class, ['label' => 'Delivery Date From', 'required' => false, 'attr' => ['placeholder' => 'Delivery Date From...', 'data-date-format' => 'mm/dd/yyyy']])
            ->add('deliveryDateTo', TextType::class, ['label' => 'Delivery Date To', 'required' => false, 'attr' => ['placeholder' => 'Delivery Date To...', 'data-date-format' => 'mm/dd/yyyy']])
            ->add('deliveryDate', TextType::class, ['label' => 'Delivery Date', 'required' => false, 'attr' => ['placeholder' => 'Select Delivery Date...', 'data-date-format' => 'mm/dd/yyyy']])
            ->getForm();
    }


}
