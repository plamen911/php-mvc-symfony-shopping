<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Photo;
use AppBundle\Entity\Product;
use AppBundle\Entity\Department;
use AppBundle\Entity\Tag;
use AppBundle\Form\ProductType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Product controller.
 * @package AppBundle\Controller
 * @author Plamen Markov <plamen@lynxlake.org>
 *
 * @Route("admin/departments/{departmentId}/categories/{categoryId}/products", requirements={"departmentId": "\d+", "categoryId": "\d+"})
 */
class ProductController extends Controller
{
    /**
     * Lists all category entities.
     *
     * @Route("/", name="product_index")
     * @Method("GET")
     *
     * @param int $departmentId
     * @param int $categoryId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($departmentId = 0, $categoryId = 0)
    {
        /**
         * @var \Doctrine\ORM\EntityManager $em
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * @var \AppBundle\Entity\Category $category
         */
        $category = $em->getRepository(Category::class)->find((int)$categoryId);
        if ($category === null) {
            throw $this->createNotFoundException('Category does not exist!');
        }

        /**
         * @var \AppBundle\Entity\Department $department
         */
        $department = $this->getDoctrine()->getRepository(Department::class)->find((int)$departmentId);
        if ($department === null) {
            throw $this->createNotFoundException('Department does not exist!');
        }

        if ($category->getDepartmentId() != $department->getId()) {
            throw $this->createNotFoundException('It seems that the category does not belong to department!');
        }

        $products = $em->getRepository('AppBundle:Product')->findBy(['categoryId' => $category->getId()], ['position' => 'ASC']);

        return $this->render('admin/product/index.html.twig', [
            'department' => $department,
            'category' => $category,
            'products' => $products,
        ]);
    }

    /**
     * Creates a new product entity.
     *
     * @Route("/new", name="product_new")
     * @Method({"POST"})
     *
     * @param int $departmentId
     * @param int $categoryId
     * @return JsonResponse
     */
    public function newAction($departmentId = 0, $categoryId = 0)
    {
        $category = $this->getDoctrine()->getRepository(Category::class)->find((int)$categoryId);
        if ($category === null) {
            return new JsonResponse([
                'code' => 1,
                'message' => 'Category does not exist!',
                'data' => []
            ]);
        }

        if ($category->getDepartmentId() != $departmentId) {
            return new JsonResponse([
                'code' => 1,
                'message' => 'It seems that the category does not belong to department!',
                'data' => []
            ]);
        }

        $product = new Product();
        $product->setCategoryId($category->getId());
        $product->setCategory($category);
        $product->setName('Unspecified');
        $product->setPosition($this->getMaxPosition($categoryId) + 1);

        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->flush();

        return new JsonResponse([
            'code' => 0,
            'message' => 'ok',
            'data' => ['id' => $product->getId(),
                'departmentId' => $category->getDepartmentId(),
                'categoryId' => $category->getId(),
                'name' => $product->getName()
            ]
        ]);
    }

    /**
     * Displays a form to edit an existing product entity.
     *
     * @Route("/{id}/edit", name="product_edit", requirements={"id": "\d+"})
     * @Method({"GET","POST"})
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id = 0, Request $request)
    {
        /**
         * @var \Doctrine\ORM\EntityManager $em
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * @var \AppBundle\Entity\Product $product
         */
        $product = $em->getRepository(Product::class)->find($id);

        if($request->isXmlHttpRequest()) {
            if ($product === null) {
                return new JsonResponse([
                    'code' => 1,
                    'message' => 'Product not found',
                    'data' => []
                ]);
            }

            $product->setName($request->get('name'));

            $tagsString = $request->get('tags');
            $tags = $this->getTags($em, $tagsString);

            $product->setTags($tags);

            $em->persist($product);
            $em->flush();

            return new JsonResponse([
                'code' => 0,
                'message' => 'ok',
                'data' => ['id' => $product->getId(), 'categoryId' => $product->getCategoryId()]
            ]);

        } else {

            if ($product === null) {
                throw $this->createNotFoundException('Product not found');
            }

            $tags = $product->getTags();
            $tagsString = implode(', ', $tags->toArray());

            $editForm = $this->createForm(ProductType::class, $product);
            // $editForm = $this->createForm(ProductType::class, $product, ['attr' => ['departmentId' => $product->getCategory()->getDepartmentId()]]);
            // $editForm = $this->createForm(ProductType::class, $product, ['departmentId' => $product->getCategory()->getDepartmentId()]);
            $editForm->handleRequest($request);

            if ($editForm->isSubmitted() && $editForm->isValid()) {
                $tagsString = $request->get('tags');
                $tags = $this->getTags($em, $tagsString);
                $product->setTags($tags);

                /**
                 * @var UploadedFile $file
                 */
                foreach ($request->files->get('photos') as $file) {
                    if ($file === null) continue;

                    $fileName = str_replace(' ', '_', $file->getClientOriginalName());
                    if (preg_match('/.*?\.(gif|png|jpg|jpeg)$/i', $fileName, $matches)) {
                        $fileName = substr($fileName, 0, strrpos($fileName, '.')) . '_' . time() . '.' . $matches[1];
                    }

                    // move takes the target directory and then the
                    // target filename to move to
                    $file->move(Photo::getUploadDirOriginals(), $fileName);

                    // Image resize
                    Photo::resizeImage(Photo::getUploadDirOriginals() . $fileName, Photo::getUploadDirThumbs() . $fileName, 100, 100);
                    Photo::resizeImage(Photo::getUploadDirOriginals() . $fileName, Photo::getUploadDirLarge() . $fileName, 1120, 720);

                    $photo = new Photo();
                    $photo->setProductId($product->getId());
                    $photo->setProduct($product);
                    $photo->setFileName($fileName);
                    $photo->setCaption('');
                    $photo->setPosition($this->getPhotoMaxPosition($product->getId()) + 1);

                    $em->persist($photo);
                    $em->flush();
                }

                // update photo captions
                foreach ($request->request->all() as $key => $item) {
                    if (preg_match('/^caption_(\d+)$/', $key, $matches)) {
                        $photo = $em->getRepository('AppBundle:Photo')->find($matches[1]);
                        if ($photo !== null) {
                            $photo->setCaption(trim($item));
                            $em->persist($photo);
                            $em->flush();
                        }
                    }
                }

                $product->setPhoto($this->getMainPhoto($product->getId()));

                $em->persist($product);
                $em->flush();

                $this->addFlash('success', 'Product successfully saved.');
                return $this->redirectToRoute('product_edit', [
                        'departmentId' => $product->getCategory()->getDepartmentId(),
                        'categoryId' => $product->getCategoryId(),
                        'id' => $product->getId()
                    ]
                );
            }

            return $this->render('admin/product/edit.html.twig', [
                'department' => $product->getCategory()->getDepartment(),
                'category' => $product->getCategory(),
                'departments' => $em->getRepository(Department::class)->findBy([], ['position' => 'ASC']),
                'product' => $product,
                'form' => $editForm->createView(),
                'tags' => $tagsString,
                'uploadDirLarge' => Photo::getUploadDirLarge()
            ]);
        }
    }

    /**
     * Sort categories with drag and drop.
     *
     * @Route("/sort", name="product_sort")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sortAction(Request $request)
    {
        $sorted = $request->get('item', []);
        if (!empty($sorted)) {
            /**
             * @var \Doctrine\ORM\EntityManager $em
             */
            $em = $this->getDoctrine()->getManager();
            foreach ($sorted as $i => $item_id) {
                /**
                 * @var \AppBundle\Entity\Product $product
                 */
                $product = $this->getDoctrine()->getRepository(Product::class)->find($item_id);
                if ($product) {
                    $product->setPosition($i + 1);
                    $em->persist($product);
                    $em->flush();
                }
            }
        }

        return new JsonResponse([
            'code' => 0,
            'message' => 'ok',
            'data' => []
        ]);
    }

    /**
     * Sort departments with drag and drop.
     *
     * @Route("/sort-photos", name="photo_sort")
     * @Method({"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sortPhotosAction(Request $request)
    {
        $sorted = $request->get('image', []);
        if (!empty($sorted)) {
            /**
             * @var \Doctrine\ORM\EntityManager $em
             */
            $em = $this->getDoctrine()->getManager();
            /**
             * @var \AppBundle\Entity\Product $product
             */
            $product = null;
            foreach ($sorted as $i => $item_id) {
                /**
                 * @var \AppBundle\Entity\Photo $photo
                 */
                $photo = $this->getDoctrine()->getRepository(Photo::class)->find($item_id);
                if ($photo) {
                    $product = $photo->getProduct();

                    $photo->setPosition($i + 1);
                    $em->persist($photo);
                    $em->flush();
                }
            }

            if ($product !== null) {
                $product->setPhoto($this->getMainPhoto($product->getId()));
                $em->persist($product);
                $em->flush();
            }
        }

        return new JsonResponse([
            'code' => 0,
            'message' => 'ok',
            'data' => []
        ]);
    }

    /**
     * Delete photo.
     *
     * @Route("/{productId}/photo/{photoId}", name="photo_delete", requirements={"productId": "\d+", "photoId": "\d+"})
     * @Method({"POST"})
     *
     * @param int $productId
     * @param int $photoId
     * @return JsonResponse
     */
    public function deletePhoto($productId = 0, $photoId = 0)
    {
        /**
         * @var \Doctrine\ORM\EntityManager $em
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * @var \AppBundle\Entity\Product $product
         */
        $product = $em->getRepository(Product::class)->find($productId);
        if ($product === null) {
            return new JsonResponse([
                'code' => 1,
                'message' => 'Product not found',
                'data' => []
            ]);
        }

        /**
         * @var \AppBundle\Entity\Photo $photo
         */
        $photo = $em->getRepository(Photo::class)->find($photoId);
        if ($photo === null) {
            return new JsonResponse([
                'code' => 1,
                'message' => 'Photo cannot be found.',
                'data' => []
            ]);
        }

        $product->removePhoto($photo);

        $product->setPhoto($this->getMainPhoto($product->getId()));
        $em->persist($product);
        $em->flush();

        return new JsonResponse([
            'code' => 0,
            'message' => 'ok',
            'data' => []
        ]);
    }

    /**
     * Deletes a product entity.
     *
     * @Route("/{id}", name="product_delete", requirements={"id": "\d+"})
     * @Method("DELETE")
     *
     * @param int $id
     * @return JsonResponse
     */
    public function deleteAction($id = 0)
    {
        /**
         * @var \Doctrine\ORM\EntityManager $em
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * @var \AppBundle\Entity\Product $product
         */
        $product = $em->getRepository(Product::class)->find((int)$id);
        if ($product === null) {
            return new JsonResponse([
                'code' => 1,
                'message' => 'Product not found',
                'data' => []
            ]);
        }

        /**
         * @var \AppBundle\Entity\Photo $photo
         */
        foreach ($product->getPhotos() as $photo) {
            $product->removePhoto($photo);
        }

        $em->remove($product);
        $em->flush();

        return new JsonResponse([
            'code' => 0,
            'message' => 'ok',
            'data' => []
        ]);
    }

    /**
     * @Route("/{productId}/new-department/{newDepartmentId}", name="change_department", requirements={"productId": "\d+", "newDepartmentId": "\d+"})
     * @Method("POST")
     *
     * @param int $productId
     * @param int $newDepartmentId
     * @return JsonResponse
     */
    public function changeDepartmentAction($productId = 0, $newDepartmentId = 0)
    {
        /**
         * @var \Doctrine\ORM\EntityManager $em
         */
        $em = $this->getDoctrine()->getManager();

        /**
         * @var \AppBundle\Entity\Product $product
         */
        $product = $em->getRepository(Product::class)->find($productId);
        if ($product === null) {
            return new JsonResponse([
                'code' => 1,
                'message' => 'Product not found',
                'data' => []
            ]);
        }

        /**
         * @var \AppBundle\Entity\Department $department
         */
        $department = $em->getRepository(Department::class)->find($newDepartmentId);
        if ($department === null) {
            return new JsonResponse([
                'code' => 1,
                'message' => 'Department not found',
                'data' => []
            ]);
        }

        /**
         * @var \AppBundle\Entity\Category $category
         */
        $category = $department->getCategories()->first();

        if ($category === null || !is_object($category)) {
            return new JsonResponse([
                'code' => 1,
                'message' => 'This department has no categories',
                'data' => []
            ]);
        }

        $product->setCategory($category);
        $product->setCategoryId($category->getId());

        $em->persist($product);
        $em->flush();

        return new JsonResponse([
            'code' => 0,
            'message' => 'ok',
            'data' => [
                'url' => $this->generateUrl('product_edit', [
                        'departmentId' => $department->getId(),
                        'categoryId' => $category->getId(),
                        'id' => $product->getId()
                    ])
            ]
        ]);
    }

    /**
     * @param EntityManager $em
     * @param string $tagsString
     * @return ArrayCollection
     */
    public function getTags(EntityManager $em, $tagsString = '')
    {
        $tags = explode(',', $tagsString);
        $tagRepo = $this->getDoctrine()->getRepository(Tag::class);
        $tagsToSave = new ArrayCollection();

        foreach ($tags as $tagName) {

            $tagName = trim($tagName);
            $tag = $tagRepo->findOneBy(['name' => $tagName]);

            if ($tag === null) {
                $tag = new Tag();
                $tag->setName($tagName);
                $em->persist($tag);
            }

            $tagsToSave->add($tag);
        }

        return $tagsToSave;
    }

    /**
     * Creates a form to delete a product entity.
     *
     * @param Product $product The product entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Product $product)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('product_delete', ['id' => $product->getId()]))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    private function getMaxPosition($categoryId = 0)
    {
        /**
         * @var \Doctrine\ORM\EntityManager $em
         */
        $em = $this->getDoctrine()->getManager();
        /**
         * @var \AppBundle\Entity\Product $product
         */
        $product = $em->getRepository('AppBundle:Product')->findOneBy(['categoryId' => $categoryId], ['position' => 'DESC']);

        return ($product) ? $product->getPosition() : 0;
    }

    private function getPhotoMaxPosition($productId = 0)
    {
        /**
         * @var \Doctrine\ORM\EntityManager $em
         */
        $em = $this->getDoctrine()->getManager();
        /**
         * @var \AppBundle\Entity\Photo $photo
         */
        $photo = $em->getRepository('AppBundle:Photo')->findOneBy(['productId' => $productId], ['position' => 'DESC']);

        return ($photo) ? $photo->getPosition() : 0;
    }

    private function getMainPhoto($productId = 0)
    {
        /**
         * @var \Doctrine\ORM\EntityManager $em
         */
        $em = $this->getDoctrine()->getManager();
        /**
         * @var \AppBundle\Entity\Photo $photo
         */
        $photo = $em->getRepository('AppBundle:Photo')->findOneBy(['productId' => $productId], ['position' => 'ASC']);

        return ($photo) ? $photo->getFileName() : 0;
    }
}
