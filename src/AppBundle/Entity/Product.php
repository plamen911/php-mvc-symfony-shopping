<?php

namespace AppBundle\Entity;

use AppBundle\AppBundle;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product
 *
 * @ORM\Table(name="products")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProductRepository")
 */
class Product
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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_taxable", type="boolean", nullable=true)
     */
    private $isTaxable;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

    /**
     * @var int
     *
     * @ORM\Column(name="qty", type="integer", nullable=true)
     */
    private $qty;

    /**
     * @var string
     *
     * @ORM\Column(name="dimension", type="string", length=255, nullable=true)
     */
    private $dimension;

    /**
     * @var string
     *
     * @ORM\Column(name="availability", type="string", length=255, nullable=true)
     */
    private $availability;

    /**
     * @var string
     *
     * @ORM\Column(name="availability_days", type="string", length=255, nullable=true)
     */
    private $availabilityDays;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position;

    /**
     * @var int
     *
     * @ORM\Column(name="category_id", type="integer")
     */
    private $categoryId;

    /**
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Category", inversedBy="products")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Photo", mappedBy="product", cascade={"persist", "remove"}, orphanRemoval=true)
     * @OrderBy({"position" = "ASC"})
     */
    private $photos;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\Tag", inversedBy="products")
     * @ORM\JoinTable(name="products_tags",
     *     joinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="tag_id", referencedColumnName="id")}
     *     )
     */
    private $tags;

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->photos = new ArrayCollection();
        $this->position = 0;
        $this->isTaxable = 0;
        $this->tags = new ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return Product
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set isTaxable
     *
     * @param boolean $isTaxable
     *
     * @return Product
     */
    public function setIsTaxable($isTaxable)
    {
        $this->isTaxable = $isTaxable;

        return $this;
    }

    /**
     * Get isTaxable
     *
     * @return bool
     */
    public function getIsTaxable()
    {
        return $this->isTaxable;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return Product
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set qty
     *
     * @param integer $qty
     *
     * @return Product
     */
    public function setQty($qty)
    {
        $this->qty = $qty;

        return $this;
    }

    /**
     * Get qty
     *
     * @return int
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * Set dimension
     *
     * @param string $dimension
     *
     * @return Product
     */
    public function setDimension($dimension)
    {
        $this->dimension = $dimension;

        return $this;
    }

    /**
     * Get dimension
     *
     * @return string
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    /**
     * Set availability
     *
     * @param string $availability
     *
     * @return Product
     */
    public function setAvailability($availability)
    {
        $this->availability = $availability;

        return $this;
    }

    /**
     * Get availability
     *
     * @return string
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Product
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param int $categoryId
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return ArrayCollection
     */
    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * @param ArrayCollection $photos
     */
    public function setPhotos($photos)
    {
        $this->photos = $photos;
    }

    /**
     * @return string
     */
    public function getAvailabilityDays()
    {
        return $this->availabilityDays;
    }

    /**
     * @param string $availabilityDays
     */
    public function setAvailabilityDays($availabilityDays)
    {
        $this->availabilityDays = $availabilityDays;
    }

    /**
     * @param \AppBundle\Entity\Photo $photo
     */
    public function removePhoto($photo)
    {
        if (!empty($photo->getFileName())) {
            if (file_exists(Photo::getUploadDirLarge() . $photo->getFileName())) {
                unlink(Photo::getUploadDirLarge() . $photo->getFileName());
            }
            if (file_exists(Photo::getUploadDirThumbs() . $photo->getFileName())) {
                unlink(Photo::getUploadDirThumbs() . $photo->getFileName());
            }
            if (file_exists(Photo::getUploadDirOriginals() . $photo->getFileName())) {
                unlink(Photo::getUploadDirOriginals() . $photo->getFileName());
            }
        }

        $this->photos->removeElement($photo);
        $photo->setProduct(null);
    }

    /**
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param ArrayCollection $tags
     */
    public function setTags(ArrayCollection $tags)
    {
        $this->tags = $tags;
    }
}

