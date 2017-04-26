<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * StoreOrderProduct
 *
 * @ORM\Table(name="store_order_products")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StoreOrderProductRepository")
 */
class StoreOrderProduct
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
     * @ORM\Column(name="name", type="string", length=255)
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
     * @ORM\Column(name="old_price", type="float", nullable=true)
     */
    private $oldPrice;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

    /**
     * @var float
     *
     * @ORM\Column(name="taxes", type="float", nullable=true)
     */
    private $taxes;

    /**
     * @var float
     *
     * @ORM\Column(name="total", type="float", nullable=true)
     */
    private $total;

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
     * @ORM\Column(name="description", type="text", length=255, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="photo", type="string", length=255, nullable=true)
     */
    private $photo;

    /**
     * @var string
     *
     * @ORM\Column(name="department_name", type="string", length=255, nullable=true)
     */
    private $departmentName;

    /**
     * @var string
     *
     * @ORM\Column(name="category_name", type="string", length=255, nullable=true)
     */
    private $categoryName;

    /**
     * @var int
     *
     * @ORM\Column(name="department_id", type="integer", nullable=true)
     */
    private $departmentId;

    /**
     * @var int
     *
     * @ORM\Column(name="category_id", type="integer", nullable=true)
     */
    private $categoryId;

    /**
     * @var int
     *
     * @ORM\Column(name="delivery_id", type="integer")
     */
    private $deliveryId;

    /**
     * @var StoreOrderDelivery
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\StoreOrderDelivery", inversedBy="products", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="delivery_id", referencedColumnName="id", nullable=false)
     */
    private $delivery;

    /**
     * StoreOrderProduct constructor.
     */
    public function __construct()
    {
        $this->oldPrice = 0;
        $this->price = 0;
        $this->qty = 0;
        $this->isTaxable = false;
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
     * @return StoreOrderProduct
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
     * @return bool
     */
    public function isIsTaxable()
    {
        return $this->isTaxable;
    }

    /**
     * @param bool $isTaxable
     */
    public function setIsTaxable($isTaxable)
    {
        $this->isTaxable = $isTaxable;
    }

    /**
     * @return float
     */
    public function getOldPrice()
    {
        return $this->oldPrice;
    }

    /**
     * @param float $oldPrice
     */
    public function setOldPrice($oldPrice)
    {
        $this->oldPrice = $oldPrice;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = (float)$price;
    }

    /**
     * @return int
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param int $qty
     */
    public function setQty($qty)
    {
        $this->qty = (int)$qty;
    }

    /**
     * @return string
     */
    public function getDimension()
    {
        return $this->dimension;
    }

    /**
     * @param string $dimension
     */
    public function setDimension($dimension)
    {
        $this->dimension = $dimension;
    }

    /**
     * @return string
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * @param string $availability
     */
    public function setAvailability($availability)
    {
        $this->availability = $availability;
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
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param string $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    /**
     * @return string
     */
    public function getDepartmentName()
    {
        return $this->departmentName;
    }

    /**
     * @param string $departmentName
     */
    public function setDepartmentName($departmentName)
    {
        $this->departmentName = $departmentName;
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        return $this->categoryName;
    }

    /**
     * @param string $categoryName
     */
    public function setCategoryName($categoryName)
    {
        $this->categoryName = $categoryName;
    }

    /**
     * @return int
     */
    public function getDepartmentId()
    {
        return $this->departmentId;
    }

    /**
     * @param int $departmentId
     */
    public function setDepartmentId($departmentId)
    {
        $this->departmentId = $departmentId;
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
     * @return int
     */
    public function getDeliveryId()
    {
        return $this->deliveryId;
    }

    /**
     * @param int $deliveryId
     */
    public function setDeliveryId($deliveryId)
    {
        $this->deliveryId = $deliveryId;
    }

    /**
     * @return StoreOrderDelivery
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * @param StoreOrderDelivery $delivery
     */
    public function setDelivery(StoreOrderDelivery $delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * @param float $total
     */
    public function setTotal(float $total)
    {
        $this->total = (float)$total;
    }

    /**
     * @return float
     */
    public function getTaxes()
    {
        return $this->taxes;
    }

    /**
     * @param float $taxes
     */
    public function setTaxes(float $taxes)
    {
        $this->taxes = $taxes;
    }
}

