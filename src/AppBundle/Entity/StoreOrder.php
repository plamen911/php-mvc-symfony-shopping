<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\OrderBy;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * StoreOrder
 *
 * @package AppBundle\Entity
 * @author Plamen Markov <plamen@lynxlake.org>
 *
 * @ORM\Table(name="store_orders")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\StoreOrderRepository")
 */
class StoreOrder
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
     * @ORM\Column(name="order_num", type="string", length=255, unique=true)
     */
    private $orderNum;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="order_date", type="datetime")
     */
    private $orderDate;

    /**
     * @var int
     *
     * @ORM\Column(name="qty", type="integer")
     */
    private $qty;

    /**
     * @var float
     *
     * @ORM\Column(name="taxes", type="float")
     */
    private $taxes;

    /**
     * @var float
     *
     * @ORM\Column(name="delivery", type="float")
     */
    private $delivery;

    /**
     * @var float
     *
     * @ORM\Column(name="subtotal", type="float")
     */
    private $subtotal;

    /**
     * @var float
     *
     * @ORM\Column(name="total", type="float")
     */
    private $total;

    /**
     * @var string
     *
     * @ORM\Column(name="billing_first_name", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $billingFirstName;

    /**
     * @var string
     *
     * @ORM\Column(name="billing_last_name", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $billingLastName;

    /**
     * @var string
     *
     * @ORM\Column(name="billing_email", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $billingEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="billing_phone", type="string", length=255)
     */
    private $billingPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="billing_address", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $billingAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="billing_address2", type="string", length=255)
     */
    private $billingAddress2;

    /**
     * @var string
     *
     * @ORM\Column(name="billing_city", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $billingCity;

    /**
     * @var string
     *
     * @ORM\Column(name="billing_state", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $billingState;

    /**
     * @var string
     *
     * @ORM\Column(name="billing_zip", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $billingZip;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_first_name", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $shippingFirstName;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_last_name", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $shippingLastName;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_email", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $shippingEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_phone", type="string", length=255)
     */
    private $shippingPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_address", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $shippingAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_address2", type="string", length=255)
     */
    private $shippingAddress2;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_city", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $shippingCity;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_state", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $shippingState;

    /**
     * @var string
     *
     * @ORM\Column(name="shipping_zip", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $shippingZip;

    /**
     * @var string
     *
     * @ORM\Column(name="credit_card_type", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $creditCardType;

    /**
     * @var string
     *
     * @ORM\Column(name="credit_card_number", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $creditCardNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="credit_card_exp_date", type="string", length=255)
     */
    private $creditCardExpDate;

    /**
     * @var string
     * @Assert\NotBlank(message="This field is required.")
     */
    private $creditCardYear;

    /**
     * @var string
     * @Assert\NotBlank(message="This field is required.")
     */
    private $creditCardMonth;

    /**
     * @var string
     * @Assert\NotBlank(message="This field is required.")
     */
    private $creditCardCode;

    /**
     * @var string
     *
     * @ORM\Column(name="credit_card_name", type="string", length=255)
     * @Assert\NotBlank(message="This field is required.")
     */
    private $creditCardName;

    /**
     * @var int
     *
     * @ORM\Column(name="order_status", type="integer")
     */
    private $orderStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_address", type="string", length=255)
     */
    private $ipAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="pgw_response", type="text")
     */
    private $pgwResponse;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User", inversedBy="orders")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\StoreOrderDelivery", mappedBy="order", cascade={"persist", "remove"})
     * @OrderBy({"deliveryDate" = "ASC"})
     */
    private $deliveries;

    /**
     * StoreOrder constructor.
     */
    public function __construct()
    {
        $this->orderDate = new \DateTime('now');
        $this->deliveries = new ArrayCollection();
        $this->orderStatus = 0;
        $this->orderNum = '';
        $this->billingAddress2 = '';
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
     * Set orderNum
     *
     * @param string $orderNum
     *
     * @return StoreOrder
     */
    public function setOrderNum($orderNum)
    {
        $this->orderNum = $orderNum;

        return $this;
    }

    /**
     * Get orderNum
     *
     * @return string
     */
    public function getOrderNum()
    {
        return $this->orderNum;
    }

    /**
     * Set orderDate
     *
     * @param \DateTime $orderDate
     *
     * @return StoreOrder
     */
    public function setOrderDate($orderDate)
    {
        $this->orderDate = $orderDate;

        return $this;
    }

    /**
     * Get orderDate
     *
     * @return \DateTime
     */
    public function getOrderDate()
    {
        return $this->orderDate;
    }

    /**
     * Set qty
     *
     * @param integer $qty
     *
     * @return StoreOrder
     */
    public function setQty($qty)
    {
        $this->qty = (int)$qty;

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
     * Set taxes
     *
     * @param float $taxes
     *
     * @return StoreOrder
     */
    public function setTaxes($taxes)
    {
        $this->taxes = (float)$taxes;

        return $this;
    }

    /**
     * Get taxes
     *
     * @return float
     */
    public function getTaxes()
    {
        return $this->taxes;
    }

    /**
     * Set shipping
     *
     * @param float $delivery
     *
     * @return StoreOrder
     */
    public function setDelivery($delivery)
    {
        $this->delivery = (float)$delivery;

        return $this;
    }

    /**
     * Get shipping
     *
     * @return float
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * Set subtotal
     *
     * @param float $subtotal
     *
     * @return StoreOrder
     */
    public function setSubtotal($subtotal)
    {
        $this->subtotal = (float)$subtotal;

        return $this;
    }

    /**
     * Get subtotal
     *
     * @return float
     */
    public function getSubtotal()
    {
        return $this->subtotal;
    }

    /**
     * Set total
     *
     * @param float $total
     *
     * @return StoreOrder
     */
    public function setTotal($total)
    {
        $this->total = (float)$total;

        return $this;
    }

    /**
     * Get total
     *
     * @return float
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set billingFirstName
     *
     * @param string $billingFirstName
     *
     * @return StoreOrder
     */
    public function setBillingFirstName($billingFirstName)
    {
        $this->billingFirstName = $billingFirstName;

        return $this;
    }

    /**
     * Get billingFirstName
     *
     * @return string
     */
    public function getBillingFirstName()
    {
        return $this->billingFirstName;
    }

    /**
     * Set billingLastName
     *
     * @param string $billingLastName
     *
     * @return StoreOrder
     */
    public function setBillingLastName($billingLastName)
    {
        $this->billingLastName = $billingLastName;

        return $this;
    }

    /**
     * Get billingLastName
     *
     * @return string
     */
    public function getBillingLastName()
    {
        return $this->billingLastName;
    }

    /**
     * Set billingEmail
     *
     * @param string $billingEmail
     *
     * @return StoreOrder
     */
    public function setBillingEmail($billingEmail)
    {
        $this->billingEmail = $billingEmail;

        return $this;
    }

    /**
     * Get billingEmail
     *
     * @return string
     */
    public function getBillingEmail()
    {
        return $this->billingEmail;
    }

    /**
     * Set billingPhone
     *
     * @param string $billingPhone
     *
     * @return StoreOrder
     */
    public function setBillingPhone($billingPhone)
    {
        $this->billingPhone = $billingPhone;

        return $this;
    }

    /**
     * Get billingPhone
     *
     * @return string
     */
    public function getBillingPhone()
    {
        return $this->billingPhone;
    }

    /**
     * Set billingAddress
     *
     * @param string $billingAddress
     *
     * @return StoreOrder
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;

        return $this;
    }

    /**
     * Get billingAddress
     *
     * @return string
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * Set billingAddress2
     *
     * @param string $billingAddress2
     *
     * @return StoreOrder
     */
    public function setBillingAddress2($billingAddress2)
    {
        $this->billingAddress2 = $billingAddress2;

        return $this;
    }

    /**
     * Get billingAddress2
     *
     * @return string
     */
    public function getBillingAddress2()
    {
        return $this->billingAddress2;
    }

    /**
     * Set billingCity
     *
     * @param string $billingCity
     *
     * @return StoreOrder
     */
    public function setBillingCity($billingCity)
    {
        $this->billingCity = $billingCity;

        return $this;
    }

    /**
     * Get billingCity
     *
     * @return string
     */
    public function getBillingCity()
    {
        return $this->billingCity;
    }

    /**
     * Set billingState
     *
     * @param string $billingState
     *
     * @return StoreOrder
     */
    public function setBillingState($billingState)
    {
        $this->billingState = $billingState;

        return $this;
    }

    /**
     * Get billingState
     *
     * @return string
     */
    public function getBillingState()
    {
        return $this->billingState;
    }

    /**
     * Set billingZip
     *
     * @param string $billingZip
     *
     * @return StoreOrder
     */
    public function setBillingZip($billingZip)
    {
        $this->billingZip = $billingZip;

        return $this;
    }

    /**
     * Get billingZip
     *
     * @return string
     */
    public function getBillingZip()
    {
        return $this->billingZip;
    }

    /**
     * Set shippingFirstName
     *
     * @param string $shippingFirstName
     *
     * @return StoreOrder
     */
    public function setShippingFirstName($shippingFirstName)
    {
        $this->shippingFirstName = $shippingFirstName;

        return $this;
    }

    /**
     * Get shippingFirstName
     *
     * @return string
     */
    public function getShippingFirstName()
    {
        return $this->shippingFirstName;
    }

    /**
     * Set shippingLastName
     *
     * @param string $shippingLastName
     *
     * @return StoreOrder
     */
    public function setShippingLastName($shippingLastName)
    {
        $this->shippingLastName = $shippingLastName;

        return $this;
    }

    /**
     * Get shippingLastName
     *
     * @return string
     */
    public function getShippingLastName()
    {
        return $this->shippingLastName;
    }

    /**
     * Set shippingEmail
     *
     * @param string $shippingEmail
     *
     * @return StoreOrder
     */
    public function setShippingEmail($shippingEmail)
    {
        $this->shippingEmail = $shippingEmail;

        return $this;
    }

    /**
     * Get shippingEmail
     *
     * @return string
     */
    public function getShippingEmail()
    {
        return $this->shippingEmail;
    }

    /**
     * Set shippingPhone
     *
     * @param string $shippingPhone
     *
     * @return StoreOrder
     */
    public function setShippingPhone($shippingPhone)
    {
        $this->shippingPhone = $shippingPhone;

        return $this;
    }

    /**
     * Get shippingPhone
     *
     * @return string
     */
    public function getShippingPhone()
    {
        return $this->shippingPhone;
    }

    /**
     * Set shippingAddress
     *
     * @param string $shippingAddress
     *
     * @return StoreOrder
     */
    public function setShippingAddress($shippingAddress)
    {
        $this->shippingAddress = $shippingAddress;

        return $this;
    }

    /**
     * Get shippingAddress
     *
     * @return string
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * Set shippingAddress2
     *
     * @param string $shippingAddress2
     *
     * @return StoreOrder
     */
    public function setShippingAddress2($shippingAddress2)
    {
        $this->shippingAddress2 = $shippingAddress2;

        return $this;
    }

    /**
     * Get shippingAddress2
     *
     * @return string
     */
    public function getShippingAddress2()
    {
        return $this->shippingAddress2;
    }

    /**
     * Set shippingCity
     *
     * @param string $shippingCity
     *
     * @return StoreOrder
     */
    public function setShippingCity($shippingCity)
    {
        $this->shippingCity = $shippingCity;

        return $this;
    }

    /**
     * Get shippingCity
     *
     * @return string
     */
    public function getShippingCity()
    {
        return $this->shippingCity;
    }

    /**
     * Set shippingState
     *
     * @param string $shippingState
     *
     * @return StoreOrder
     */
    public function setShippingState($shippingState)
    {
        $this->shippingState = $shippingState;

        return $this;
    }

    /**
     * Get shippingState
     *
     * @return string
     */
    public function getShippingState()
    {
        return $this->shippingState;
    }

    /**
     * Set shippingZip
     *
     * @param string $shippingZip
     *
     * @return StoreOrder
     */
    public function setShippingZip($shippingZip)
    {
        $this->shippingZip = $shippingZip;

        return $this;
    }

    /**
     * Get shippingZip
     *
     * @return string
     */
    public function getShippingZip()
    {
        return $this->shippingZip;
    }

    /**
     * Set creditCardType
     *
     * @param string $creditCardType
     *
     * @return StoreOrder
     */
    public function setCreditCardType($creditCardType)
    {
        $this->creditCardType = $creditCardType;

        return $this;
    }

    /**
     * Get creditCardType
     *
     * @return string
     */
    public function getCreditCardType()
    {
        return $this->creditCardType;
    }

    /**
     * Set creditCardNumber
     *
     * @param string $creditCardNumber
     *
     * @return StoreOrder
     */
    public function setCreditCardNumber($creditCardNumber)
    {
        $this->creditCardNumber = str_repeat('*', strlen($creditCardNumber) - 4) . substr($creditCardNumber, -4);
        return $this;
    }

    /**
     * Get creditCardNumber
     *
     * @return string
     */
    public function getCreditCardNumber()
    {
        return $this->creditCardNumber;
    }

    /**
     * Set creditCardExpDate
     **
     * @param string $creditCardExpDate
     *
     * @return StoreOrder
     */
    public function setCreditCardExpDate($creditCardExpDate)
    {
        $this->creditCardExpDate = $creditCardExpDate;

        return $this;
    }

    /**
     * Get creditCardExpDate
     *
     * @return string
     */
    public function getCreditCardExpDate()
    {
        return $this->creditCardExpDate;
    }

    /**
     * Set creditCardYear
     *
     * @param string $creditCardYear
     *
     * @return StoreOrder
     */
    public function setCreditCardYear($creditCardYear)
    {
        $this->creditCardYear = $creditCardYear;

        return $this;
    }

    /**
     * Get creditCardYear
     *
     * @return string
     */
    public function getCreditCardYear()
    {
        return $this->creditCardYear;
    }

    /**
     * Set creditCardMonth
     *
     * @param string $creditCardMonth
     *
     * @return StoreOrder
     */
    public function setCreditCardMonth($creditCardMonth)
    {
        $this->creditCardMonth = $creditCardMonth;

        return $this;
    }

    /**
     * Get creditCardMonth
     *
     * @return string
     */
    public function getCreditCardMonth()
    {
        return $this->creditCardMonth;
    }

    /**
     * Set creditCardName
     *
     * @param string $creditCardName
     *
     * @return StoreOrder
     */
    public function setCreditCardName($creditCardName)
    {
        $this->creditCardName = $creditCardName;

        return $this;
    }

    /**
     * Get creditCardName
     *
     * @return string
     */
    public function getCreditCardName()
    {
        return $this->creditCardName;
    }

    /**
     * Set orderStatus
     *
     * @param integer $orderStatus
     *
     * @return StoreOrder
     */
    public function setOrderStatus($orderStatus)
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    /**
     * Get orderStatus
     *
     * @return int
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * Set ipAddress
     *
     * @param string $ipAddress
     *
     * @return StoreOrder
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;

        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Set pgwResponse
     *
     * @param string $pgwResponse
     *
     * @return StoreOrder
     */
    public function setPgwResponse($pgwResponse)
    {
        $this->pgwResponse = $pgwResponse;

        return $this;
    }

    /**
     * Get pgwResponse
     *
     * @return string
     */
    public function getPgwResponse()
    {
        return $this->pgwResponse;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return ArrayCollection|StoreOrderDelivery[]
     */
    public function getDeliveries()
    {
        return $this->deliveries;
    }

    /**
     * @param ArrayCollection $deliveries
     */
    public function setDeliveries($deliveries)
    {
        $this->deliveries = $deliveries;
    }

    /**
     * @param StoreOrderDelivery $delivery
     */
    public function addDelivery($delivery)
    {
        $this->deliveries->add($delivery);
    }

    /**
     * @return string
     */
    public function getCreditCardCode()
    {
        return $this->creditCardCode;
    }

    /**
     * @param string $creditCardCode
     */
    public function setCreditCardCode($creditCardCode)
    {
        $this->creditCardCode = $creditCardCode;
    }
}

