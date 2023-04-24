<?php

namespace App\Model\Order\Entity\Order;

use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Card\Entity\Reserve\ZapCardReserve;
use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\AddReason\OrderAddReason;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Order\Entity\ManagerOperation\ManagerOrderOperation;
use App\Model\Order\Entity\Site\Site;
use App\Model\Shop\Entity\Delivery\Delivery;
use App\Model\Shop\Entity\PayMethod\PayMethod;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderRepository::class)
 * @ORM\Table(name="orders")
 */
class Order
{
    public const ORDER_STATUS_NEW = 1;
    public const ORDER_STATUS_WORK = 2;
    public const ORDER_STATUS_MOVED = 3;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="orderID")
     */
    private $orderID;

    /**
     * @var Site
     * @ORM\ManyToOne(targetEntity="App\Model\Order\Entity\Site\Site", inversedBy="orders")
     * @ORM\JoinColumn(name="siteID", referencedColumnName="siteID", nullable=true)
     */
    private $site;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="orders")
     * @ORM\JoinColumn(name="userID", referencedColumnName="userID", nullable=true)
     */
    private $user;

    /**
     * @var Contact
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Contact\Contact", inversedBy="orders")
     * @ORM\JoinColumn(name="user_contactID", referencedColumnName="contactID", nullable=true)
     */
    private $user_contact;

    /**
     * @var Beznal
     * @ORM\ManyToOne(targetEntity="App\Model\Beznal\Entity\Beznal\Beznal", inversedBy="orders")
     * @ORM\JoinColumn(name="user_beznalID", referencedColumnName="beznalID", nullable=true)
     */
    private $user_beznal;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="orders")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=true)
     */
    private $manager;

    /**
     * @var OrderAddReason
     * @ORM\ManyToOne(targetEntity="App\Model\Order\Entity\AddReason\OrderAddReason", inversedBy="orders")
     * @ORM\JoinColumn(name="order_add_reasonID", referencedColumnName="order_add_reasonID", nullable=true)
     */
    private $order_add_reason;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = 1;

    /**
     * @ORM\Column(type="text")
     */
    private $comment = '';

    /**
     * @var PayMethod
     * @ORM\ManyToOne(targetEntity="App\Model\Shop\Entity\PayMethod\PayMethod", inversedBy="orders")
     * @ORM\JoinColumn(name="payMethodID", referencedColumnName="payMethodID", nullable=true)
     */
    private $payMethod;

    /**
     * @ORM\Column(type="integer")
     */
    private $pay_number = 0;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $pay_id = '';

    /**
     * @var Delivery
     * @ORM\ManyToOne(targetEntity="App\Model\Shop\Entity\Delivery\Delivery", inversedBy="orders")
     * @ORM\JoinColumn(name="deliveryID", referencedColumnName="deliveryID", nullable=true)
     */
    private $delivery;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $dostavka = 0;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $predoplata = 0;

    /**
     * @ORM\Column(type="boolean", name="isOwnDelivery")
     */
    private $isOwnDelivery = false;

    /**
     * @ORM\Column(type="koef", precision=4, scale=2)
     */
    private $discount = 0;

    /**
     * @ORM\Column(type="string", length=100, name="lastOrderPage")
     */
    private $lastOrderPage = '';

    /**
     * @ORM\Column(type="integer")
     */
    private $office_id;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $vin = '';

    /**
     * @var OrderGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Good\OrderGood", cascade={"persist"}, mappedBy="order", orphanRemoval=true)
     */
    private $order_goods;

    /**
     * @var ZapCardReserve[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Reserve\ZapCardReserve", mappedBy="order")
     */
    private $zapCardReserve;

    /**
     * @var ManagerOrderOperation[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\ManagerOperation\ManagerOrderOperation", mappedBy="order")
     */
    private $manager_order_operations;

    public function __construct(User $user, ?Manager $manager, ?OrderAddReason $order_add_reason)
    {
        $this->user = $user;
        $this->manager = $manager;
        $this->order_add_reason = $order_add_reason;
        $this->dateofadded = new \DateTime();
        $this->status = self::ORDER_STATUS_WORK;
        $this->order_goods = new ArrayCollection();
        $this->zapCardReserve = new ArrayCollection();
        $this->manager_order_operations = new ArrayCollection();
    }

    public function activate()
    {
        $this->status = self::ORDER_STATUS_WORK;
    }

    public function getId(): int
    {
        return $this->orderID;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getUserContact(): ?Contact
    {
        return $this->user_contact;
    }

    public function getUserBeznal(): ?Beznal
    {
        return $this->user_beznal;
    }

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function getOrderAddReason(): ?OrderAddReason
    {
        return $this->order_add_reason;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function isNew(): bool
    {
        return $this->status === self::ORDER_STATUS_NEW;
    }

    public function isWorking(): bool
    {
        return $this->status === self::ORDER_STATUS_WORK;
    }

    public function isMoved(): bool
    {
        return $this->status === self::ORDER_STATUS_MOVED;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getPayMethod(): ?PayMethod
    {
        return $this->payMethod;
    }

    public function getPayNumber(): int
    {
        return $this->pay_number;
    }

    public function getPayId(): string
    {
        return $this->pay_id;
    }

    public function getDelivery(): ?Delivery
    {
        return $this->delivery;
    }

    public function getDostavka(): string
    {
        return $this->dostavka;
    }

    public function getPredoplata(): string
    {
        return $this->predoplata;
    }

    public function getIsOwnDelivery(): bool
    {
        return $this->isOwnDelivery;
    }

    public function getDiscount(): ?string
    {
        return $this->discount;
    }

    public function getLastOrderPage(): string
    {
        return $this->lastOrderPage;
    }

    public function getOfficeId(): ?int
    {
        return $this->office_id;
    }

    public function getVin(): string
    {
        return $this->vin;
    }

    public function getDeliverySum(): string
    {
        if ($this->dostavka == 0) {
            if ($this->isOwnDelivery) return 'client'; else return 'free';
        }
        return $this->dostavka;
    }

    /**
     * @return OrderGood[]|ArrayCollection
     */
    public function getOrderGoods()
    {
        return $this->order_goods->toArray();
    }

    public function assignOrderGood(OrderGood $orderGood): void
    {
        $this->order_goods->add($orderGood);
    }

    public function removeOrderGood(OrderGood $orderGood)
    {
        if ($this->order_goods->contains($orderGood)) {
            $this->order_goods->removeElement($orderGood);
        }
    }
}
