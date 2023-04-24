<?php

namespace App\Model\Card\Entity\Reserve;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Income\Entity\Income\Income;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Order\Entity\Order\Order;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZapCardReserveRepository::class)
 * @ORM\Table(name="zapCardReserve")
 */
class ZapCardReserve
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="reserveID")
     */
    private $reserveID;

    /**
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad", inversedBy="zapCardReserve")
     * @ORM\JoinColumn(name="zapSkladID", referencedColumnName="zapSkladID", nullable=false)
     */
    private $zapSklad;

    /**
     * @var Income
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Income\Income", inversedBy="zapCardReserve")
     * @ORM\JoinColumn(name="incomeID", referencedColumnName="incomeID", nullable=false)
     */
    private $income;

    /**
     * @var DetailNumber
     * @ORM\Column(type="detail_number", length=30)
     */
    private $number;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateofclosed;

    /**
     * @var Order
     * @ORM\ManyToOne(targetEntity="App\Model\Order\Entity\Order\Order", inversedBy="zapCardReserve")
     * @ORM\JoinColumn(name="orderID", referencedColumnName="orderID", nullable=false)
     */
    private $order;

    /**
     * @var OrderGood
     * @ORM\ManyToOne(targetEntity="App\Model\Order\Entity\Good\OrderGood", inversedBy="zapCardReserve")
     * @ORM\JoinColumn(name="goodID", referencedColumnName="goodID", nullable=false)
     */
    private $order_good;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="zapCardReserve")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=false)
     */
    private $manager;

    public function __construct(ZapSklad $zapSklad, Income $income, DetailNumber $number, int $quantity, ?\DateTime $dateofclosed, Order $order, OrderGood $orderGood, Manager $manager)
    {
        $this->zapSklad = $zapSklad;
        $this->income = $income;
        $this->number = $number;
        $this->quantity = $quantity;
        $this->dateofadded = new \DateTime();
        $this->dateofclosed = $dateofclosed;
        $this->order = $order;
        $this->order_good = $orderGood;
        $this->manager = $manager;
    }

    public function updateQuantity(int $quantity)
    {
        $this->quantity = $quantity;
    }

    public function updateZapSklad(ZapSklad $zapSklad)
    {
        $this->zapSklad = $zapSklad;
    }

    public function updateDateOfClosed(?\DateTime $dateofclosed = null)
    {
        $this->dateofclosed = $dateofclosed;
    }

    public function getId(): ?int
    {
        return $this->reserveID;
    }

    public function getZapSklad(): ZapSklad
    {
        return $this->zapSklad;
    }

    public function getIncome(): Income
    {
        return $this->income;
    }

    public function getNumber(): DetailNumber
    {
        return $this->number;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getDateofadded(): ?\DateTime
    {
        return $this->dateofadded;
    }

    public function getDateofclosed(): ?\DateTime
    {
        if ($this->dateofclosed && $this->dateofclosed->format('Y') == '-0001') return null;
        return $this->dateofclosed;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getOrderGood(): OrderGood
    {
        return $this->order_good;
    }

    public function getManager(): Manager
    {
        return $this->manager;
    }
}
