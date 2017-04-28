<?php

namespace AppBundle\Controller;

use AppBundle\Entity\StoreOrder;
use AppBundle\Form\ProfileType;
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

        $page = (null == $request->query->get('page')) ? 1 : (int)$request->query->get('page');
        $limit = (null == $request->query->get('limit')) ? 20 : (int)$request->query->get('limit');

        /**
         * @var \Doctrine\ORM\Tools\Pagination\Paginator $storeOrders
         */
        $storeOrders = $em->getRepository(StoreOrder::class)
            ->search($params, $page, $limit);

        $maxPages = (int)ceil($storeOrders->count() / $limit);
        $thisPage = $page;

        /**
         * @var \AppBundle\Entity\StoreOrder $orders[]
         */
        $orders = $storeOrders->getIterator();

        return $this->render('account/orders.html.twig', [
            'form' => $form->createView(),
            // Pass through the 3 above variables to calculate pages in twig
            'orders' => $orders,
            'maxPages' => $maxPages,
            'thisPage' => $thisPage,
        ]);
    }

    /**
     * Display order details.
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/orders/{id}", name="account_order_details", requirements={"id": "\d+"})
     * @Method("GET")
     *
     * @param StoreOrder $storeOrder
     * @param Request $request
     * @return Response
     */
    public function orderDetailsAction(StoreOrder $storeOrder, Request $request)
    {
        return $this->render('account/order.html.twig', [
            'order' => $storeOrder
        ]);
    }

    /**
     * Display user profile form.
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/", name="account_profile")
     * @Method({"GET","POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function profileAction(Request $request)
    {
        /**
         * @var \AppBundle\Entity\User $user
         */
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user, ['states' => $this->get('app.common')->getStates()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $oldPassword = $request->get('oldPassword');
            $newPassword = $request->get('newPassword');

            // Change user password
            if (!empty($oldPassword) && !empty($newPassword)) {
                $encoder = $this->get('security.password_encoder');
                if (!$encoder->isPasswordValid($user, $oldPassword)) {
                    $this->addFlash('alert', 'Wrong old password!');
                    return $this->render('account/profile.html.twig', [
                        'form' => $form->createView()
                    ]);
                }
                $user->setPassword($encoder->encodePassword($user, $newPassword));
            }

            /**
             * @var \Doctrine\Common\Persistence\ObjectManager $em
             */
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Your profile was successfully updated.');
            $this->redirectToRoute('account_profile');
        }

        return $this->render('account/profile.html.twig', [
            'form' => $form->createView()
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
