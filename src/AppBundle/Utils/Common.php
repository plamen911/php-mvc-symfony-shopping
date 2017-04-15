<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 4/14/17
 * Time: 22:17
 */

namespace AppBundle\Utils;

use AppBundle\Entity\Department;

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

}