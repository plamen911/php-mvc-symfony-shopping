<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 4/14/17
 * Time: 22:17
 */

namespace AppBundle\Utils;

use AppBundle\Entity\Department;
use Symfony\Component\HttpFoundation\Request;

class Common
{
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $em
     * @return Department[]
     */
    public function getDepartmentsInMenu($em)
    {
        return $em->getRepository(Department::class)
            ->findBy(['showInMenu' => true], ['position' => 'ASC']);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getSessionCart(Request $request) {
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        if (empty($cart)) {
            $cart['items'] = [];
            $cart['qty'] = 0;
            $cart['subtotal'] = 0;
            $cart['taxes'] = 0;
            $cart['delivery'] = 0;
            $cart['discount'] = 0;
            $cart['total'] = 0;
        }

        return $cart;
    }

}