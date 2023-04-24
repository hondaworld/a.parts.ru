<?php

namespace App\Model\Expense\Entity\Expense;

use App\Model\Income\Entity\Income\Income;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExpenseRepository::class)
 * @ORM\Table(name="expense")
 */
class Expense
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="expenseID")
     */
    private $expenseID;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @var OrderGood
     * @ORM\ManyToOne(targetEntity="App\Model\Order\Entity\Good\OrderGood", inversedBy="expenses")
     * @ORM\JoinColumn(name="goodID", referencedColumnName="goodID", nullable=false)
     */
    private $order_good;

    /**
     * @var Income
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Income\Income", inversedBy="expenses")
     * @ORM\JoinColumn(name="incomeID", referencedColumnName="incomeID", nullable=true)
     */
    private $income;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    public function __construct(OrderGood $orderGood, Income $income, int $quantity)
    {
        $this->dateofadded = new \DateTime();
        $this->order_good = $orderGood;
        $this->income = $income;
        $this->quantity = $quantity;
    }

    public function expense(?ZapSklad $zapSklad)
    {
        $income = $this->income;
        $income->expense($this->quantity);
        if ($zapSklad) {
            $incomeSklad = $income->getSkladByZapSklad($zapSklad);
        } else {
            $incomeSklad = $income->getFirstSklad();
        }
        $incomeSklad->expense($this->quantity);
        $income->removeZapCardReserveByOrderGood($this->order_good);
    }

    public function getId(): ?int
    {
        return $this->expenseID;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getOrderGood(): OrderGood
    {
        return $this->order_good;
    }

    public function getIncome(): Income
    {
        return $this->income;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
