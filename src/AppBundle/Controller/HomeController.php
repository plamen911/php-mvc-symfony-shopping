<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Role;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Home controller.
 * @package AppBundle\Controller
 * @author Plamen Markov <plamen@lynxlake.org>
 */
class HomeController extends Controller
{
    private $defaultAdminUsername = 'plamen@lynxlake.org';
    private $defaultAdminPassword = '1';
    private $defaultRoles = ['ROLE_ADMIN', 'ROLE_EDITOR', 'ROLE_USER'];

    /**
     * @Route("/seed")
     * @return Response
     */
    public function seedData()
    {
        /**
         * @var \Doctrine\Common\Persistence\ObjectManager $em
         */
        $em = $this->getDoctrine()->getManager();

        // Create default roles
        foreach ($this->defaultRoles as $roleName) {
            $role = $em->getRepository(Role::class)->findOneBy(['name' => $roleName]);
            if (!$role) {
                $role = new Role();
                $role->setName($roleName);
                $em->persist($role);
                $em->flush();
            }
        }

        // Create admin user
        $user = $em->getRepository(User::class)->findOneBy(['email' => $this->defaultAdminUsername]);
        if (!$user) {
            $user = new User();
            $user->setFirstName('Plamen');
            $user->setLastName('Markov');
            $user->setEmail($this->defaultAdminUsername);

            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $this->defaultAdminPassword);
            $user->setPassword($password);

            $adminRole = $em->getRepository(Role::class)->findOneBy(['name' => 'ROLE_ADMIN']);
            $user->addRole($adminRole);

            $em->persist($user);
            $em->flush();
        }

        return new Response('Data seed done!');
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("/about", name="about")
     */
    public function aboutsAction()
    {
        return $this->render('home/about.html.twig');
    }

    /**
     * @Route("/privacy", name="privacy")
     */
    public function privacyAction()
    {
        return $this->render('home/privacy.html.twig');
    }
}
