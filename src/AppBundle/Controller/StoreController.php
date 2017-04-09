<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Department;
use AppBundle\Entity\Photo;
use AppBundle\Entity\Product;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Home controller.
 * @package AppBundle\Controller
 * @author Plamen Markov <plamen@lynxlake.org>
 *
 * @Route("store")
 */
class StoreController extends Controller
{
    /**
     * @Route("/", name="store_index")
     */
    public function indexAction()
    {
        /**
         * @var \Doctrine\ORM\EntityManager $em
         */
        $em = $this->getDoctrine()->getManager();

        $departments = $em->getRepository(Department::class)
            ->findBy(['showInMenu' => true], ['position' => 'ASC']);

        return $this->render('store/index.html.twig', [
            'departmentsInMenu' => $departments
        ]);
    }

    /**
     * @Route("/search", name="store_search_by_keyword")
     * @Method({"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        return $this->render('search/index.html.twig',
            $this->getSearchResults($request->get('keyword', ''))
        );
    }

    /**
     * @Route("/search/department/{id}", name="store_search_by_department")
     * @Method({"GET"})
     *
     * @param Department $department
     * @return \Symfony\Component\HttpFoundation\Response
     * @internal param Request $request
     */
    public function searchByDepartment(Department $department)
    {
        if ($department == null) {
            throw $this->createNotFoundException('Department not found');
        }

        $data = $this->getSearchResults('department', $department->getId());
        $data['keyword'] = '';

        return $this->render('search/index.html.twig', $data);
    }

    /**
     * @Route("/search/category/{id}", name="store_search_by_category")
     * @Method({"GET"})
     *
     * @param Category $category
     * @return \Symfony\Component\HttpFoundation\Response
     * @internal param Request $request
     */
    public function searchByCategory(Category $category)
    {
        if ($category == null) {
            throw $this->createNotFoundException('Category not found');
        }

        $data = $this->getSearchResults('category', $category->getId());
        $data['keyword'] = '';

        return $this->render('search/index.html.twig', $data);
    }

    /**
     * @param string $term
     * @param string $value
     * @return array
     */
    private function getSearchResults($term = '', $value = '')
    {
        /**
         * @var \Doctrine\ORM\EntityManager $em
         */
        $em = $this->getDoctrine()->getManager();

        $departments = $em->getRepository(Department::class)
            ->findBy(['showInMenu' => true], ['position' => 'ASC']);

        $data = $em->getRepository(Product::class)
            ->findAllByKeyword($term, $value);

        return [
            'departmentsInMenu' => $departments,
            'items' => $data->items,
            'departments' => $data->departments,
            'categories' => $data->categories,
            'keyword' => $data->keyword,
            'uploadDirLarge' => Photo::getUploadDirLarge()
        ];
    }
}
