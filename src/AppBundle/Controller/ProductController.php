<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Category;
use AppBundle\Entity\Photo;
use AppBundle\Entity\Product;
use AppBundle\Entity\Department;
use AppBundle\Form\ProductType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Product controller.
 *
 * @Route("admin/department/{departmentId}/category/{categoryId}/products")
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
        $category = $this->getDoctrine()->getRepository(Category::class)->find((int)$categoryId);
        if ($category === null) {
            throw $this->createNotFoundException('Category does not exist!');
        }

        $department = $this->getDoctrine()->getRepository(Department::class)->find((int)$departmentId);
        if ($department === null) {
            throw $this->createNotFoundException('Department does not exist!');
        }

        if ($category->getDepartmentId() != $department->getId()) {
            throw $this->createNotFoundException('It seems that the category does not belong to department!');
        }

        $em = $this->getDoctrine()->getManager();
        $products = $em->getRepository('AppBundle:Product')->findBy(['categoryId' => $category->getId()], ['position' => 'ASC']);

        return $this->render('admin/product/index.html.twig', array(
            'department' => $department,
            'category' => $category,
            'products' => $products,
        ));
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
     * @Route("/{id}/edit", name="product_edit")
     * @Method({"GET","POST"})
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction($id = 0, Request $request)
    {
        /**
         * @var \AppBundle\Entity\Product $product
         */
        $product = $this->getDoctrine()->getRepository(Product::class)->find($id);

        if($request->isXmlHttpRequest()) {
            if ($product === null) {
                return new JsonResponse([
                    'code' => 1,
                    'message' => 'Product not found',
                    'data' => []
                ]);
            }

            $product->setName($request->get('name'));

            $em = $this->getDoctrine()->getManager();
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

            $editForm = $this->createForm(ProductType::class, $product);
            $editForm->handleRequest($request);

            //dump($editForm->isValid());

            if ($editForm->isSubmitted() && $editForm->isValid()) {
                $em = $this->getDoctrine()->getManager();

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

                $em->persist($product);
                $em->flush();

                $this->addFlash('success', 'Product successfully saved.');
                return $this->redirectToRoute('product_edit', array(
                        'departmentId' => $product->getCategory()->getDepartmentId(),
                        'categoryId' => $product->getCategoryId(),
                        'id' => $product->getId()
                    )
                );
            }

            return $this->render('admin/product/edit.html.twig', array(
                'department' => $product->getCategory()->getDepartment(),
                'category' => $product->getCategory(),
                'product' => $product,
                'form' => $editForm->createView(),
                'uploadDirLarge' => Photo::getUploadDirLarge()
            ));
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
            $em = $this->getDoctrine()->getManager();
            foreach ($sorted as $i => $item_id) {
                /**
                 * @var \AppBundle\Entity\Photo $photo
                 */
                $photo = $this->getDoctrine()->getRepository(Photo::class)->find($item_id);
                if ($photo) {
                    $photo->setPosition($i + 1);
                    $em->persist($photo);
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
     * Delete photo.
     *
     * @Route("/{productId}/photo/{photoId}", name="photo_delete")
     * @Method({"POST"})
     *
     * @param int $productId
     * @param int $photoId
     * @return JsonResponse
     */
    public function deletePhoto($productId = 0, $photoId = 0)
    {
        /**
         * @var \AppBundle\Entity\Product $product
         */
        $product = $this->getDoctrine()->getRepository(Product::class)->find($productId);
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
        $photo = $this->getDoctrine()->getRepository(Photo::class)->find($photoId);
        if ($photo === null) {
            return new JsonResponse([
                'code' => 1,
                'message' => 'Photo cannot be found.',
                'data' => []
            ]);
        }

        dump($product);
        dump($photo);

        $product->removePhoto($photo);

        $em = $this->getDoctrine()->getManager();
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
     * @Route("/{id}", name="product_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Product $product)
    {
        $form = $this->createDeleteForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($product);
            $em->flush();
        }

        return $this->redirectToRoute('product_index');
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
            ->setAction($this->generateUrl('product_delete', array('id' => $product->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    private function getMaxPosition($categoryId = 0) {
        $em = $this->getDoctrine()->getManager();
        $product = $em->getRepository('AppBundle:Product')->findOneBy(['categoryId' => $categoryId], ['position' => 'DESC']);

        /**
         * @var \AppBundle\Entity\Product $product
         */
        return ($product) ? $product->getPosition() : 0;
    }

    private function getPhotoMaxPosition($productId = 0) {
        $em = $this->getDoctrine()->getManager();
        $photo = $em->getRepository('AppBundle:Photo')->findOneBy(['productId' => $productId], ['position' => 'DESC']);

        /**
         * @var \AppBundle\Entity\Product $photo
         */
        return ($photo) ? $photo->getPosition() : 0;
    }
}
