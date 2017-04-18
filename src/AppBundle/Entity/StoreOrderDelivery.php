<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;

/**
 * StoreOrderDelivery
 *
 * @ORM\Table(name="store_order_deliveries")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StoreOrderDeliveryRepository")
 */
class StoreOrderDelivery
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
     * @var float
     *
     * @ORM\Column(name="cost", type="float")
     */
    private $cost;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="delivery_date", type="datetime")
     */
    private $deliveryDate;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_method", type="string", length=255)
     */
    private $deliveryMethod;

    /**
     * @var int
     *
     * @ORM\Column(name="order_id", type="integer")
     */
    private $orderId;

    /**
     * @var StoreOrder
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\StoreOrder", inversedBy="deliveries")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     */
    private $order;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\StoreOrderProduct", mappedBy="delivery")
     * @OrderBy({"id" = "ASC"})
     */
    private $products;

    /**
     * StoreOrderDelivery constructor.
     */
    public function __construct()
    {
        $this->cost = 0;
        $this->products = new ArrayCollection();
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
     * Set cost
     *
     * @param float $cost
     *
     * @return StoreOrderDelivery
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return float
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set deliveryDate
     *
     * @param \DateTime $deliveryDate
     *
     * @return StoreOrderDelivery
     */
    public function setDeliveryDate($deliveryDate)
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    /**
     * Get deliveryDate
     *
     * @return \DateTime
     */
    public function getDeliveryDate()
    {
        return $this->deliveryDate;
    }

    /**
     * Set deliveryMethod
     *
     * @param string $deliveryMethod
     *
     * @return StoreOrderDelivery
     */
    public function setDeliveryMethod($deliveryMethod)
    {
        $this->deliveryMethod = $deliveryMethod;

        return $this;
    }

    /**
     * Get deliveryMethod
     *
     * @return string
     */
    public function getDeliveryMethod()
    {
        return $this->deliveryMethod;
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @param int $orderId
     */
    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
    }

    /**
     * @return StoreOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param StoreOrder $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return ArrayCollection|StoreOrderProduct[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param ArrayCollection|StoreOrderProduct[] $products
     */
    public function setProducts($products)
    {
        $this->products = $products;
    }

    /**
     * @param StoreOrderProduct $product
     */
    public function addProduct($product)
    {
        $this->products->add($product);
    }
}

