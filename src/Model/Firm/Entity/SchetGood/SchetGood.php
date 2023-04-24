<?php

namespace App\Model\Firm\Entity\SchetGood;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Firm\Entity\Schet\Schet;
use App\Model\Order\Entity\Good\OrderGood;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SchetGoodRepository::class)
 * @ORM\Table(name="schet_goods")
 */
class SchetGood
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="schet_goodID")
     */
    private $schet_goodID;

    /**
     * @var OrderGood
     * @ORM\OneToOne(targetEntity="App\Model\Order\Entity\Good\OrderGood", inversedBy="schet_good")
     * @ORM\JoinColumn(name="goodID", referencedColumnName="goodID", nullable=false)
     */
    private $order_good;

    /**
     * @var Schet
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Schet\Schet", inversedBy="schet_goods")
     * @ORM\JoinColumn(name="schetID", referencedColumnName="schetID", nullable=false)
     */
    private $schet;

    /**
     * @var DetailNumber
     * @ORM\Column(type="detail_number", length=30)
     */
    private $number;

    /**
     * @var Creater
     * @ORM\ManyToOne(targetEntity="App\Model\Detail\Entity\Creater\Creater", inversedBy="schet_goods")
     * @ORM\JoinColumn(name="createrID", referencedColumnName="createrID", nullable=false)
     */
    private $creater;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $price;

    public function __construct(Schet $schet, OrderGood $order_good, DetailNumber $number, Creater $creater, int $quantity, string $price)
    {
        $this->schet = $schet;
        $this->order_good = $order_good;
        $this->number = $number;
        $this->creater = $creater;
        $this->quantity = $quantity;
        $this->price = $price;
    }

    public function getId(): ?int
    {
        return $this->schet_goodID;
    }

    public function getOrderGood(): OrderGood
    {
        return $this->order_good;
    }

    public function getSchet(): Schet
    {
        return $this->schet;
    }

    public function getNumber(): DetailNumber
    {
        return $this->number;
    }

    public function getCreater(): Creater
    {
        return $this->creater;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getPrice(): string
    {
        return $this->price;
    }
}
