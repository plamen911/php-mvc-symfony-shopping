<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 4/26/17
 * Time: 13:22
 */

namespace AppBundle\Utils;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginatorHelper
{
    /**
     * https://anil.io/blog/symfony/doctrine/symfony-and-doctrine-pagination-with-twig/
     * Paginator Helper
     *
     * Pass through a query object, current page & limit
     * the offset is calculated from the page and limit
     * returns an `Paginator` instance, which you can call the following on:
     *
     *     $paginator->getIterator()->count() # Total fetched (ie: `5` posts)
     *     $paginator->count() # Count of ALL posts (ie: `20` posts)
     *     $paginator->getIterator() # ArrayIterator
     *
     * @param Query|QueryBuilder $dql DQL Query Object
     * @param integer $page Current page (defaults to 1)
     * @param integer $limit The total number per page (defaults to 5)
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function paginate($dql, $page = 1, $limit = 2)
    {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1))// Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }
}