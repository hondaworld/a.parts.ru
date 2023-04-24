<?php

namespace App\Model\Expense\Entity\Shipping;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\ShippingPlace\ShippingPlace;
use App\Model\Expense\Entity\ShippingStatus\ShippingStatus;
use App\Model\Shop\Entity\Delivery\Delivery;
use App\Model\Shop\Entity\DeliveryTk\DeliveryTk;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShippingRepository::class)
 * @ORM\Table(name="shippings")
 */
class Shipping
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="shippingID")
     */
    private $shippingID;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="shippings")
     * @ORM\JoinColumn(name="userID", referencedColumnName="userID", nullable=false)
     */
    private $user;

    /**
     * @var ExpenseDocument
     * @ORM\ManyToOne(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", inversedBy="shippings")
     * @ORM\JoinColumn(name="expenseDocumentID", referencedColumnName="expenseDocumentID", nullable=false)
     */
    private $expenseDocument;

    /**
     * @var ShippingStatus
     * @ORM\ManyToOne(targetEntity="App\Model\Expense\Entity\ShippingStatus\ShippingStatus", inversedBy="shippings", fetch="EAGER")
     * @ORM\JoinColumn(name="status", referencedColumnName="status", nullable=false)
     */
    private $status;

    /**
     * @ORM\Column(type="integer")
     */
    private $pay_type = 0;

    /**
     * @var Delivery
     * @ORM\ManyToOne(targetEntity="App\Model\Shop\Entity\Delivery\Delivery", inversedBy="shippings")
     * @ORM\JoinColumn(name="deliveryID", referencedColumnName="deliveryID", nullable=true)
     */
    private $delivery;

    /**
     * @var DeliveryTk
     * @ORM\ManyToOne(targetEntity="App\Model\Shop\Entity\DeliveryTk\DeliveryTk", inversedBy="shippings")
     * @ORM\JoinColumn(name="delivery_tkID", referencedColumnName="delivery_tkID", nullable=true)
     */
    private $delivery_tk;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $tracknumber = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nakladnaya = '';

    /**
     * @ORM\Column(type="date")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var ShippingPlace[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\ShippingPlace\ShippingPlace", mappedBy="shipping", orphanRemoval=true, cascade={"persist"})
     */
    private $places;

    public function __construct(ExpenseDocument $expenseDocument, ShippingStatus $shippingStatus)
    {
        $this->expenseDocument = $expenseDocument;
        $this->user = $expenseDocument->getExpUser();
        $this->status = $shippingStatus;
        $this->dateofadded = new \DateTime();
        $this->places = new ArrayCollection();
    }

    public function updateStatus(ShippingStatus $status): void
    {
        $this->status = $status;
    }

    public function updateNakladnaya(string $nakladnaya): void
    {
        $this->nakladnaya = $nakladnaya;
    }

    public function updateDelivery(\DateTime $dateofadded, ?DeliveryTk $deliveryTk, ?string $tracknumber, ?int $pay_type): void
    {
        $this->dateofadded = $dateofadded;
        $this->delivery_tk = $deliveryTk;
        $this->tracknumber = $tracknumber ?: '';
        $this->pay_type = $pay_type ?: 0;
    }

    public function removeNakladnaya(): void
    {
        $this->nakladnaya = '';
    }

    public function getId(): ?int
    {
        return $this->shippingID;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getExpenseDocument(): ?ExpenseDocument
    {
        return $this->expenseDocument;
    }

    public function getStatus(): ShippingStatus
    {
        return $this->status;
    }

    public function getPayType(): ?int
    {
        return $this->pay_type;
    }

    public function getDelivery(): ?Delivery
    {
        return $this->delivery;
    }

    public function getDeliveryTk(): ?DeliveryTk
    {
        return $this->delivery_tk;
    }

    public function getTracknumber(): string
    {
        return $this->tracknumber;
    }

    public function getNakladnaya(): string
    {
        return $this->nakladnaya;
    }

    public function getDateofadded(): ?\DateTime
    {
        return $this->dateofadded;
    }

    public function isHide(): bool
    {
        return $this->isHide;
    }

    /**
     * @return ShippingPlace[]|ArrayCollection
     */
    public function getPlaces()
    {
        return $this->places->toArray();
    }

    public function addPlace(ShippingPlace $shippingPlace): void
    {
        $shippingPlace->updateShipping($this);
        $this->places->add($shippingPlace);
    }
}
