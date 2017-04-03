<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Home controller.
 * @package AppBundle\Controller
 * @author Plamen Markov <plamen@lynxlake.org>
 */
class HomeController extends Controller
{
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
