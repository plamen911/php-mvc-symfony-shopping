<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use AppBundle\Form\ProfileType;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AdminController
 * @package AppBundle\Controller
 * @author Plamen Markov <plamen@lynxlake.org>
 *
 * @Route("admin")
 */
class AdminController extends Controller
{
    /**
     * Display store users.
     *
     * @Route("/users", name="admin_users")
     * @Method("GET")
     *
     * @param Request $request
     * @return Response
     */
    public function usersAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_EDITOR'], null, 'Unable to access this page!');

        /**
         * @var \Doctrine\Common\Persistence\ObjectManager $em
         */
        $em = $this->getDoctrine()->getManager();

        if ($request->query->get('ban')) {
            $userId = (int)$request->query->get('ban');
            if ($this->setBanned($em, $userId, true)) {
                return $this->redirectToRoute('admin_users');
            }
        } elseif ($request->query->get('unban')) {
            $userId = (int)$request->query->get('unban');
            if ($this->setBanned($em, $userId, false)) {
                return $this->redirectToRoute('admin_users');
            }
        }

        /**
         * @var \AppBundle\Entity\User[] $users
         */
        $users = $em->getRepository(Role::class)->findOneBy(['name' => 'ROLE_USER'])->getUsers();

        return $this->render('admin/users/users.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * Display store editors.
     *
     * @Route("/editors", name="admin_editors")
     * @Method("GET")
     *
     * @param Request $request
     * @return Response
     */
    public function editorsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN'], null, 'Unable to access this page!');

        /**
         * @var \Doctrine\Common\Persistence\ObjectManager $em
         */
        $em = $this->getDoctrine()->getManager();

        if ($request->query->get('ban')) {
            $userId = (int)$request->query->get('ban');
            if ($this->setBanned($em, $userId, false)) {
                return $this->redirectToRoute('admin_editors');
            }
        } elseif ($request->query->get('unban')) {
            $userId = (int)$request->query->get('unban');
            if ($this->setBanned($em, $userId, true)) {
                return $this->redirectToRoute('admin_editors');
            }
        }

        /**
         * @var \AppBundle\Entity\User[] $users
         */
        $users = $em->getRepository(Role::class)->findOneBy(['name' => 'ROLE_EDITOR'])->getUsers();

        return $this->render('admin/users/editors.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * Edit store user.
     *
     * @Route("/user/edit/{id}", name="admin_user_edit", requirements={"id": "\d+"})
     * @Method({"GET","POST"})
     *
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function editUserAction(User $user, Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_EDITOR'], null, 'Unable to access this page!');

        $form = $this->createForm(ProfileType::class, $user, [
            'states' => $this->get('app.common')->getStates(),
            'action' => $this->generateUrl('admin_user_edit', ['id' => $user->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $request->get('password');
            if (!empty($password)) {
                $encoder = $this->get('security.password_encoder');
                $user->setPassword($encoder->encodePassword($user, $password));
            }

            /**
             * @var \Doctrine\ORM\EntityManager $em
             */
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'User profile was successfully updated.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/edit_user.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * Edit store user.
     *
     * @Route("/user/create", name="admin_user_create")
     * @Method({"GET","POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function createUserAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_EDITOR'], null, 'Unable to access this page!');

        $password = $request->get('password');

        $user = new User();
        $user->setPassword($password);
        $form = $this->createForm(ProfileType::class, $user, [
            'states' => $this->get('app.common')->getStates(),
            'action' => $this->generateUrl('admin_user_create'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && empty($password)) {
            $this->addFlash('alert', 'Password is missing.');
            return $this->render('admin/users/create_user.html.twig', [
                'user' => $user,
                'form' => $form->createView()
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $encoder = $this->get('security.password_encoder');
            $user->setPassword($encoder->encodePassword($user, $password));

            /**
             * @var \Doctrine\ORM\EntityManager $em
             */
            $em = $this->getDoctrine()->getManager();

            $userRole = $em->getRepository(Role::class)->findOneBy(['name' => 'ROLE_USER']);
            $user->addRole($userRole);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'User profile was successfully created.');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/create_user.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * Edit store editor.
     *
     * @Route("/editor/edit/{id}", name="admin_editor_edit", requirements={"id": "\d+"})
     * @Method({"GET","POST"})
     *
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function editEditorAction(User $user, Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN'], null, 'Unable to access this page!');

        $form = $this->createForm(ProfileType::class, $user, [
            'states' => $this->get('app.common')->getStates(),
            'action' => $this->generateUrl('admin_editor_edit', ['id' => $user->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $request->get('password');
            if (!empty($password)) {
                $encoder = $this->get('security.password_encoder');
                $user->setPassword($encoder->encodePassword($user, $password));
            }

            /**
             * @var \Doctrine\ORM\EntityManager $em
             */
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Editor profile was successfully updated.');
            return $this->redirectToRoute('admin_editors');
        }

        return $this->render('admin/users/edit_editor.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * Edit store editor.
     *
     * @Route("/editor/create", name="admin_editor_create")
     * @Method({"GET","POST"})
     *
     * @param Request $request
     * @return Response
     */
    public function createEditorAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN'], null, 'Unable to access this page!');

        $password = $request->get('password');

        $user = new User();
        $user->setPassword($password);
        $form = $this->createForm(ProfileType::class, $user, [
            'states' => $this->get('app.common')->getStates(),
            'action' => $this->generateUrl('admin_editor_create'),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && empty($password)) {
            $this->addFlash('alert', 'Password is missing.');
            return $this->render('admin/users/create_editor.html.twig', [
                'user' => $user,
                'form' => $form->createView()
            ]);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $encoder = $this->get('security.password_encoder');
            $user->setPassword($encoder->encodePassword($user, $password));

            /**
             * @var \Doctrine\ORM\EntityManager $em
             */
            $em = $this->getDoctrine()->getManager();

            $userRole = $em->getRepository(Role::class)->findOneBy(['name' => 'ROLE_EDITOR']);
            $user->addRole($userRole);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Editor profile was successfully created.');
            return $this->redirectToRoute('admin_editors');
        }

        return $this->render('admin/users/create_editor.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @param ObjectManager $em
     * @param int $userId
     * @param bool $enabled
     * @return bool
     */
    private function setBanned(ObjectManager $em, int $userId, bool $enabled)
    {
        if ($userId != $this->getUser()->getId()) {
            /**
             * @var \AppBundle\Entity\User $user
             */
            $user = $em->getRepository(User::class)->find($userId);
            $user->setEnabled($enabled);
            $em->persist($user);
            $em->flush();

            return true;
        }

        return false;
    }
}
