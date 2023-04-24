<?php

namespace App\Model\Expense\Entity\Sklad;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\ReserveSklad\ZapCardReserveSklad;
use App\Model\Expense\Entity\SkladDocument\ExpenseSkladDocument;
use App\Model\Income\Entity\Income\Income;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExpenseSkladRepository::class)
 * @ORM\Table(name="expense_sklad")
 */
class ExpenseSklad
{
    public const ADDED = 0;
    public const SENT = 1;
    public const INCOME = 2;
    public const PACKED = 3;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="expenseID")
     */
    private $expenseID;

    /**
     * @var ExpenseSkladDocument
     * @ORM\ManyToOne(targetEntity="App\Model\Expense\Entity\SkladDocument\ExpenseSkladDocument", inversedBy="expense_sklads")
     * @ORM\JoinColumn(name="expense_skladDocumentID", referencedColumnName="expense_skladDocumentID", nullable=true)
     */
    private $expense_skladDocument;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @var ZapCard
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Card\ZapCard", inversedBy="expense_sklads")
     * @ORM\JoinColumn(name="zapCardID", referencedColumnName="zapCardID", nullable=false)
     */
    private $zapCard;

    /**
     * @var OrderGood
     * @ORM\ManyToOne(targetEntity="App\Model\Order\Entity\Good\OrderGood", inversedBy="expense_sklads")
     * @ORM\JoinColumn(name="goodID", referencedColumnName="goodID", nullable=true)
     */
    private $order_good;

    /**
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad", inversedBy="expense_sklads")
     * @ORM\JoinColumn(name="zapSkladID", referencedColumnName="zapSkladID", nullable=false)
     */
    private $zapSklad;

    /**
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad", inversedBy="expense_sklads_to")
     * @ORM\JoinColumn(name="zapSkladID_to", referencedColumnName="zapSkladID", nullable=false)
     */
    private $zapSklad_to;

    /**
     * @var Income
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Income\Income", inversedBy="expense_sklads")
     * @ORM\JoinColumn(name="incomeID", referencedColumnName="incomeID", nullable=false)
     */
    private $income;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity = 0;

    /**
     * @ORM\Column(type="integer", name="quantityPicking")
     */
    private $quantityPicking = 0;

    /**
     * @ORM\Column(type="integer", name="quantityIncome")
     */
    private $quantityIncome = 0;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="expense_sklads")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=true)
     */
    private $manager;

    /**
     * @var ZapCardReserveSklad[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\ReserveSklad\ZapCardReserveSklad", cascade={"persist"}, orphanRemoval=true, mappedBy="expense_sklad")
     */
    private $zapCardReserveSklad;

    public function __construct(ZapCard $zapCard, ZapSklad $zapSklad, ZapSklad $zapSklad_to, Income $income, int $quantity, ?OrderGood $order_good = null)
    {
        $this->zapCard = $zapCard;
        $this->dateofadded = new \DateTime();
        $this->zapSklad = $zapSklad;
        $this->zapSklad_to = $zapSklad_to;
        $this->income = $income;
        $this->quantity = $quantity;
        $this->order_good = $order_good;
        $this->zapCardReserveSklad = new ArrayCollection();
    }

    public function pack(Manager $manager)
    {
        $this->status = ExpenseSklad::PACKED;
        $this->manager = $manager;
    }

    public function unPack()
    {
        $this->status = ExpenseSklad::ADDED;
        $this->manager = null;
        $this->quantityPicking = 0;
    }

    public function send()
    {
        $this->status = ExpenseSklad::SENT;
    }

    public function incomeOnSklad()
    {
        $this->status = ExpenseSklad::INCOME;
    }

    public function unPicking()
    {
        $this->quantityPicking = 0;
    }

    public function unIncomeFromSklad()
    {
        $this->quantityIncome = 0;
    }

    public function increaseQuantityPicking(int $quantityPicking)
    {
        $this->quantityPicking += $quantityPicking;
    }

    public function increaseQuantityIncome(int $quantityIncome)
    {
        $this->quantityIncome += $quantityIncome;
    }

    public function updateDocument(ExpenseSkladDocument $expenseSkladDocument)
    {
        $this->expense_skladDocument = $expenseSkladDocument;
    }

    public function assignZapCardReserveSklad(ZapCard $zapCard, ZapSklad $zapSklad, ZapSklad $zapSklad_to, Income $income, int $quantity, Manager $manager)
    {
        $zapCardReserveSklad = new ZapCardReserveSklad($zapCard, $zapSklad, $zapSklad_to, $income, $quantity, $this, $manager);
        $this->zapCardReserveSklad->add($zapCardReserveSklad);
    }

    public function removeReserveByZapSklad(ZapSklad $zapSklad)
    {
        foreach ($this->zapCardReserveSklad as $zapCardReserveSklad) {
            $zapCardReserveSklad->getIncome()->changeReserve(-$zapCardReserveSklad->getQuantity());
            $incomeSklad = $zapCardReserveSklad->getIncome()->getSkladByZapSklad($zapSklad);
            if ($incomeSklad) {
                $incomeSklad->changeReserve(-$zapCardReserveSklad->getQuantity());
            }
        }
        $this->zapCardReserveSklad->clear();
    }

    public function getId(): ?int
    {
        return $this->expenseID;
    }

    public function getExpenseSkladDocument(): ?ExpenseSkladDocument
    {
        return $this->expense_skladDocument;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getZapCard(): ZapCard
    {
        return $this->zapCard;
    }

    public function getOrderGood(): ?OrderGood
    {
        return $this->order_good;
    }

    public function getZapSklad(): ZapSklad
    {
        return $this->zapSklad;
    }

    public function getZapSkladTo(): ZapSklad
    {
        return $this->zapSklad_to;
    }

    public function getIncome(): Income
    {
        return $this->income;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getQuantityPicking(): int
    {
        return $this->quantityPicking;
    }

    public function getQuantityIncome(): int
    {
        return $this->quantityIncome;
    }

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function shipBetweenSklads()
    {
        $income = $this->income;
        $reserves = $this->getZapCardReserveSkladByIncome($income);
        foreach ($reserves as $reserve) {
            $income->shipBetweenSklads($this->zapSklad, $this->zapSklad_to, $reserve->getQuantity());
        }
    }

    public function shippedOnSklad()
    {
        $this->getIncome()->fromPathToSklad($this->getZapSkladTo(), $this->getQuantity());
        $this->getZapCard()->assignLocation($this->getZapSkladTo());

        foreach ($this->zapCardReserveSklad as $zapCardReserveSklad) {
            $zapCardReserveSklad->getIncome()->changeReserve(-$zapCardReserveSklad->getQuantity());
            $incomeSklad = $zapCardReserveSklad->getIncome()->getSkladByZapSklad($this->zapSklad_to);
            if ($incomeSklad) {
                $incomeSklad->changeReserve(-$zapCardReserveSklad->getQuantity());
            }
        }
        $this->zapCardReserveSklad->clear();
    }

    /**
     * @return ZapCardReserveSklad[]
     */
    public function getZapCardReserveSkladByIncome(Income $income): array
    {
        $result = [];
        foreach ($this->zapCardReserveSklad as $reserve) {
            if ($reserve->getIncome()->getId() == $income->getId()) {
                $result[] = $reserve;
            }
        }
        return $result;
    }

    /**
     * @return ZapCardReserveSklad[]|ArrayCollection
     */
    public function getZapCardReserveSklad()
    {
        return $this->zapCardReserveSklad->toArray();
    }
}
