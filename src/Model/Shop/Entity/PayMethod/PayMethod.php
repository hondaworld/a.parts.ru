<?php

namespace App\Model\Shop\Entity\PayMethod;

use App\Model\Order\Entity\Order\Order;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PayMethodRepository::class)
 * @ORM\Table(name="payMethods")
 */
class PayMethod
{
    public const CREDIT_CARD = 1;
    public const BEZNAL = 8;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="payMethodID")
     */
    private $payMethodID;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $val;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

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
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Order\Order", mappedBy="payMethod")
     */
    private $orders;

    public function __construct(string $val, ?string $description, bool $isMain, int $number)
    {
        $this->val = $val;
        $this->description = $description ?: '';
        $this->isMain = $isMain;
        $this->number = $number;
    }

    public function update(string $val, ?string $description, bool $isMain)
    {
        $this->val = $val;
        $this->description = $description ?: '';
        $this->isMain = $isMain;
    }

    public function getId(): ?int
    {
        return $this->payMethodID;
    }

    public function getVal(): string
    {
        return $this->val;
    }

    public function getDescription(): string
    {
        return $this->description;
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
