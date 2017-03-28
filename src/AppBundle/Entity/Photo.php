<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Photo
 *
 * @ORM\Table(name="photos")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PhotoRepository")
 */
class Photo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=255, nullable=true)
     *
     * @Assert\File(mimeTypes={ "image/jpg", "image/jpeg" })
     */
    private $fileName;

    /**
     * @var string
     *
     * @ORM\Column(name="caption", type="string", length=255, nullable=true)
     */
    private $caption;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position;

    /**
     * @var int
     *
     * @ORM\Column(name="product_id", type="integer")
     */
    private $productId;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Product", inversedBy="photos")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id")
     */
    private $product;

    /**
     * Photo constructor.
     */
    public function __construct()
    {
        $this->position = 0;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     *
     * @return Photo
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set caption
     *
     * @param string $caption
     *
     * @return Photo
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * Get caption
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Photo
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @param int $productId
     */
    public function setProductId($productId)
    {
        $this->productId = $productId;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

}

