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
            $cart['total'] = 0;
        }

        return $cart;
    }

    public function getStates()
    {
        $states = [
            'AK' => 'Alaska', 'AL' => 'Alabama', 'AR' => 'Arkansas',
            'AZ' => 'Arizona', 'CA' => 'California', 'CO' => 'Colorado', 'CT' => 'Connecticut',
            'DC' => 'District of Columbia', 'DE' => 'Delaware', 'FL' => 'Florida', 'GA' => 'Georgia',
            'HI' => 'Hawaii', 'IA' => 'Iowa', 'ID' => 'Idaho', 'IL' => 'Illinois',
            'IN' => 'Indiana', 'KS' => 'Kansas', 'KY' => 'Kentucky', 'LA' => 'Louisiana',
            'MA' => 'Massachusetts', 'MD' => 'Maryland', 'ME' => 'Maine', 'MI' => 'Michigan',
            'MN' => 'Minnesota', 'MO' => 'Missouri', 'MS' => 'Mississippi', 'MT' => 'Montana',
            'NC' => 'North Carolina', 'ND' => 'North Dakota', 'NE' => 'Nebraska', 'NH' => 'New Hampshire',
            'NJ' => 'New Jersey', 'NM' => 'New Mexico', 'NV' => 'Nevada', 'NY' => 'New York',
            'OH' => 'Ohio', 'OK' => 'Oklahoma', 'OR' => 'Oregon', 'PA' => 'Pennsylvania',
            'RI' => 'Rhode Island', 'SC' => 'South Carolina', 'SD' => 'South Dakota', 'TN' => 'Tennessee',
            'TX' => 'Texas', 'UT' => 'Utah', 'VA' => 'Virginia', 'VT' => 'Vermont',
            'WA' => 'Washington', 'WI' => 'Wisconsin', 'WV' => 'West Virginia', 'WY' => 'Wyoming'
        ];

        return array_flip($states);
    }

}