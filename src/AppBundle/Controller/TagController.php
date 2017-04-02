<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class TagController extends Controller
{
    /**
     * @Route("/tags/{name}", name="products_with_tag")
     * @param string $name
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function products($name = '')
    {
        $tag = $this->getDoctrine()->getRepository(Tag::class)->findOneBy(['name' => $name]);

        return $this->render('tags/products.html.twig', ['tag' => $tag]);
    }
}
