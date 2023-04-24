<?php

namespace App\Model\Card\Entity\ReserveSklad;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use App\Model\Income\Entity\Income\Income;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZapCardReserveSkladRepository::class)
 * @ORM\Table(name="zapCardReserve_sklad")
 */
class ZapCardReserveSklad
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="reserveID")
     */
    private $reserveID;

    /**
     * @var ZapCard
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Card\ZapCard", inversedBy="zapCardReserveSklad")
     * @ORM\JoinColumn(name="zapCardID", referencedColumnName="zapCardID", nullable=false)
     */
    private $zapCard;

    /**
     * @var Income
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Income\Income", inversedBy="zapCardReserveSklad")
     * @ORM\JoinColumn(name="incomeID", referencedColumnName="incomeID", nullable=false)
     */
    private $income;

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
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad", inversedBy="zapCardReserveSklad")
     * @ORM\JoinColumn(name="zapSkladID", referencedColumnName="zapSkladID", nullable=false)
     */
    private $zapSklad;

    /**
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad", inversedBy="zapCardReserveSkladTo")
     * @ORM\JoinColumn(name="zapSkladID_to", referencedColumnName="zapSkladID", nullable=false)
     */
    private $zapSklad_to;

    /**
     * @var ExpenseSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Expense\Entity\Sklad\ExpenseSklad", inversedBy="zapCardReserveSklad")
     * @ORM\JoinColumn(name="expenseID", referencedColumnName="expenseID", nullable=false)
     */
    private $expense_sklad;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="zapCardReserveSklad")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=false)
     */
    private $manager;

    public function __construct(ZapCard $zapCard, ZapSklad $zapSklad, ZapSklad $zapSklad_to, Income $income, int $quantity, ExpenseSklad $expense_sklad, Manager $manager)
    {
        $this->zapCard = $zapCard;
        $this->zapSklad = $zapSklad;
        $this->zapSklad_to = $zapSklad_to;
        $this->income = $income;
        $this->quantity = $quantity;
        $this->dateofadded = new \DateTime();
        $this->expense_sklad = $expense_sklad;
        $this->manager = $manager;
    }

    public function getId(): ?int
    {
        return $this->reserveID;
    }

    public function getZapCard(): ZapCard
    {
        return $this->zapCard;
    }

    public function getZapSklad(): ZapSklad
    {
        return $this->zapSklad;
    }

    public function getIncome(): Income
    {
        return $this->income;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getDateofclosed(): ?\DateTimeInterface
    {
        return $this->dateofclosed;
    }

    public function getZapSkladTo(): ZapSklad
    {
        return $this->zapSklad_to;
    }

    public function getExpenseSklad(): ExpenseSklad
    {
        return $this->expense_sklad;
    }

    public function getManager(): Manager
    {
        return $this->manager;
    }
}
