<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Form\SignupType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class CheckoutController
 * @package AppBundle\Controller
 * @author Plamen Markov <plamen@lynxlake.org>
 *
 * @Route("checkout")
 */
class CheckoutController extends Controller
{
    /**
     * @var string Uniquely identifies the secured area/firewall in security.yml
     */
    const providerKey = 'secured_area';

    /**
     * @Route("/authorize", name="checkout_authorize")
     * @Method({"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function authorizeAction(Request $request)
    {
        $cart = $this->get('app.common')->getSessionCart($request);

        if ($this->getUser() != null && $cart['qty'] > 0) {
            return $this->redirectToRoute('payment_index');
        }

        /**
         * @var \Doctrine\Common\Persistence\ObjectManager $em
         */
        $em = $this->getDoctrine()->getManager();

        $loginForm = $this->getLoginForm();
        $signupForm = $this->createForm(SignupType::class);

        $loginForm->handleRequest($request);
        $signupForm->handleRequest($request);

        return $this->render('checkout/authorize.html.twig', [
            'cart' => $cart,
            'departmentsInMenu' => $this->get('app.common')->getDepartmentsInMenu($em),
            'loginForm' => $loginForm->createView(),
            'signupForm' => $signupForm->createView(),
        ]);
    }

    /**
     * @Route("/login", name="checkout_login")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function loginAction(Request $request)
    {
        // custom login
        $form = $this->getLoginForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $email = (isset($data['email'])) ? $data['email'] : '';
            $plainPassword = (isset($data['password'])) ? $data['password'] : '';

            /**
             * @var \Doctrine\Common\Persistence\ObjectManager $em
             */
            $em = $this->getDoctrine()->getManager();
            /**
             * @var \AppBundle\Entity\User $user
             */
            $user = $em->getRepository(User::class)->findOneBy(['email' => $email ]);
            if (!$user) {
                $this->addFlash('alert', 'User not found.');
                return $this->authorizeAction($request);
            }

            $encoder = $this->get('security.password_encoder');
            $validPassword = $encoder->isPasswordValid($user, $plainPassword);

            if (!$validPassword) {
                $this->addFlash('alert', 'Invalid password.');
                return $this->authorizeAction($request);
            }

            $this->setUserCredentials($user, self::providerKey);

            return $this->redirectToRoute('payment_index');
        }

        return $this->redirectToRoute('checkout_authorize');
    }

    /**
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @Route("/logout", name="logout_action")
     * @Method({"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logoutAction(Request $request)
    {
        //clear the token, cancel session and redirect
        $this->get('security.token_storage')->setToken(null);
        $request->getSession()->invalidate();
        return $this->redirectToRoute('checkout_authorize');
    }

    /**
     * @Route("/signup", name="checkout_signup")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function signupAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(SignupType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $encoder = $this->get('security.password_encoder');
            $user->setPassword($encoder->encodePassword($user, $user->getPassword()));

            /**
             * @var \Doctrine\Common\Persistence\ObjectManager $em
             */
            $em = $this->getDoctrine()->getManager();

            /**
             * @var \AppBundle\Entity\Role $userRole
             */
            $userRole = $em->getRepository(Role::class)->findOneBy(['name' => 'ROLE_USER']);

            $user->addRole($userRole);
            $em->persist($user);
            $em->flush();

            $this->setUserCredentials($user, self::providerKey);

            return $this->redirectToRoute('payment_index');
        }

        return $this->authorizeAction($request);
    }

    /**
     * Creates a form to login user and proceed to payment.
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function getLoginForm()
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('checkout_login'))
            ->setMethod('POST')
            ->add('email', EmailType::class, ['label' => 'E-mail', 'attr' => ['placeholder' => 'E-mail...']])
            ->add('password', PasswordType::class, ['label' => 'Password', 'attr' => ['placeholder' => 'Password...']])
            ->getForm();
    }

    /**
     * @param \AppBundle\Entity\User $user
     * @param string $providerKey
     */
    private function setUserCredentials($user, $providerKey)
    {
        $token = new UsernamePasswordToken($user, null, $providerKey, $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_' . $providerKey, serialize($token));
    }
}
