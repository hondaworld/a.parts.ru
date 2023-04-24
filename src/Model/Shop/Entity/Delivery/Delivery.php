<?php

namespace App\Model\Shop\Entity\Delivery;

use App\Model\Expense\Entity\Shipping\Shipping;
use App\Model\Order\Entity\Order\Order;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DeliveryRepository::class)
 * @ORM\Table(name="delivery")
 */
class Delivery
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="deliveryID")
     */
    private $deliveryID;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $porog;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $x1;

    /**
     * @ORM\Column(type="boolean", name="isPercent1")
     */
    private $isPercent1;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $x2;

    /**
     * @ORM\Column(type="boolean", name="isPercent2")
     */
    private $isPercent2;

    /**
     * @ORM\Column(type="boolean", name="isTK")
     */
    private $isTK;

    /**
     * @ORM\Column(type="boolean", name="isOwnDelivery")
     */
    private $isOwnDelivery;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @var Order[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Order\Order", mappedBy="delivery")
     */
    private $orders;

    /**
     * @var Shipping[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Shipping\Shipping", mappedBy="delivery")
     */
    private $shippings;

    public function __construct(string $name, ?string $porog, ?string $x1, bool $isPercent1, ?string $x2, bool $isPercent2, bool $isTK, bool $isOwnDelivery, bool $isMain, ?string $path, int $number)
    {
        $this->name = $name;
        $this->porog = $porog;
        $this->x1 = $x1;
        $this->isPercent1 = $isPercent1;
        $this->x2 = $x2;
        $this->isPercent2 = $isPercent2;
        $this->isTK = $isTK;
        $this->isOwnDelivery = $isOwnDelivery;
        $this->isMain = $isMain;
        $this->path = $path ?: '';
        $this->number = $number;
    }

    public function update(string $name, ?string $porog, ?string $x1, bool $isPercent1, ?string $x2, bool $isPercent2, bool $isTK, bool $isOwnDelivery, bool $isMain, ?string $path)
    {
        $this->name = $name;
        $this->porog = $porog;
        $this->x1 = $x1;
        $this->isPercent1 = $isPercent1;
        $this->x2 = $x2;
        $this->isPercent2 = $isPercent2;
        $this->isTK = $isTK;
        $this->isOwnDelivery = $isOwnDelivery;
        $this->isMain = $isMain;
        $this->path = $path ?: '';
    }

    public function getId(): int
    {
        return $this->deliveryID;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPorog(): string
    {
        return $this->porog;
    }

    public function getX1(): string
    {
        return $this->x1;
    }

    public function isPercent1(): ?bool
    {
        return $this->isPercent1;
    }

    public function getX2(): string
    {
        return $this->x2;
    }

    public function isPercent2(): ?bool
    {
        return $this->isPercent2;
    }

    public function isTK(): ?bool
    {
        return $this->isTK;
    }

    public function isOwnDelivery(): ?bool
    {
        return $this->isOwnDelivery;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function changeNumber(int $number): void
    {
        $this->number = $number;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unHide(): void
    {
        $this->isHide = false;
    }
}
