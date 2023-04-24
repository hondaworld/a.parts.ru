<?php

namespace App\Model\Order\Entity\Good;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Reserve\ZapCardReserve;
use App\Model\Card\Entity\Stock\ZapCardStock;
use App\Model\Card\Entity\Stock\ZapCardStockRepository;
use App\Model\Card\Service\ZapCardPriceService;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\Expense\Expense;
use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use App\Model\Firm\Entity\Schet\Schet;
use App\Model\Firm\Entity\SchetGood\SchetGood;
use App\Model\Income\Entity\Document\IncomeDocument;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Sklad\IncomeSklad;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Alert\OrderAlert;
use App\Model\Order\Entity\AlertType\OrderAlertType;
use App\Model\Order\Entity\Order\Order;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Shop\Entity\DeleteReason\DeleteReason;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Service\Price\PartPriceService;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderGoodRepository::class)
 * @ORM\Table(name="order_goods")
 */
class OrderGood
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="goodID")
     */
    private $goodID;

    /**
     * @var Order
     * @ORM\ManyToOne(targetEntity="App\Model\Order\Entity\Order\Order", inversedBy="order_goods")
     * @ORM\JoinColumn(name="orderID", referencedColumnName="orderID", nullable=false)
     */
    private $order;

    /**
     * @var ExpenseDocument
     * @ORM\ManyToOne(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", inversedBy="order_goods")
     * @ORM\JoinColumn(name="expenseDocumentID", referencedColumnName="expenseDocumentID", nullable=true)
     */
    private $expenseDocument;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager")
     * @ORM\JoinColumn(name="expenseManagerID", referencedColumnName="managerID", nullable=true)
     */
    private $expenseManager;

    /**
     * @var IncomeDocument
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Document\IncomeDocument", inversedBy="order_goods")
     * @ORM\JoinColumn(name="incomeDocumentID", referencedColumnName="incomeDocumentID", nullable=true)
     */
    private $incomeDocument;

    /**
     * @var Schet
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Schet\Schet", inversedBy="order_goods")
     * @ORM\JoinColumn(name="schetID", referencedColumnName="schetID", nullable=true)
     */
    private $schet;

    /**
     * @var DetailNumber
     * @ORM\Column(type="detail_number", length=30)
     */
    private $number;

    /**
     * @var DetailNumber
     * @ORM\Column(type="detail_number", length=30)
     */
    private $number_old = '';

    /**
     * @var ProviderPrice
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\Price\ProviderPrice", inversedBy="order_goods")
     * @ORM\JoinColumn(name="providerPriceID", referencedColumnName="providerPriceID", nullable=true)
     */
    private $providerPrice;

    /**
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad", inversedBy="order_goods")
     * @ORM\JoinColumn(name="zapSkladID", referencedColumnName="zapSkladID", nullable=true)
     */
    private $zapSklad;

    /**
     * @var Creater
     * @ORM\ManyToOne(targetEntity="App\Model\Detail\Entity\Creater\Creater", inversedBy="order_goods")
     * @ORM\JoinColumn(name="createrID", referencedColumnName="createrID", nullable=false)
     */
    private $creater;

    /**
     * @var Income
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Income\Income", inversedBy="order_goods")
     * @ORM\JoinColumn(name="incomeID", referencedColumnName="incomeID", nullable=true)
     */
    private $income;

    /**
     * @var ZapCardStock
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Stock\ZapCardStock", inversedBy="order_goods")
     * @ORM\JoinColumn(name="stockID", referencedColumnName="stockID", nullable=true)
     */
    private $stock;

    /**
     * @ORM\Column(type="boolean")
     */
    private $no_discount = false;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $dateofdeleted;

    /**
     * @ORM\Column(type="boolean", name="isDeleted")
     */
    private $isDeleted = false;

    /**
     * @var DeleteReason
     * @ORM\ManyToOne(targetEntity="App\Model\Shop\Entity\DeleteReason\DeleteReason", inversedBy="order_goods")
     * @ORM\JoinColumn(name="deleteReasonID", referencedColumnName="deleteReasonID", nullable=true)
     */
    private $deleteReason;

    /**
     * @ORM\Column(type="boolean", name="deleteReasonEmailed")
     */
    private $deleteReasonEmailed = true;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager")
     * @ORM\JoinColumn(name="deleteManagerID", referencedColumnName="managerID", nullable=true)
     */
    private $deleteManager;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="order_goods")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=true)
     */
    private $manager;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2, name="priceZak")
     */
    private $priceZak = 0;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $price = 0;

    /**
     * @ORM\Column(type="koef", precision=4, scale=2)
     */
    private $discount = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity = 0;

    /**
     * @ORM\Column(type="integer", name="quantityReturn")
     */
    private $quantityReturn = 0;

    /**
     * @ORM\Column(type="integer", name="quantityPicking")
     */
    private $quantityPicking = 0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $page = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $returning_reason = '';

    /**
     * @ORM\Column(type="boolean", name="isHideNumber")
     */
    private $isHideNumber = false;

    /**
     * @ORM\Column(type="integer", name="isFromSite")
     */
    private $isFromSite = 1;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $comment = '';

    /**
     * @var LastIncomeStatusData
     * @ORM\Embedded(class="LastIncomeStatusData", columnPrefix=false)
     */
    private $lastIncomeStatusData;

    /**
     * @var IncomeStatus
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Status\IncomeStatus", inversedBy="order_goods")
     * @ORM\JoinColumn(name="lastIncomeStatus", referencedColumnName="status", nullable=true)
     */
    private $lastIncomeStatus;

    /**
     * @var Expense[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Expense\Expense", cascade={"persist"}, orphanRemoval=true, mappedBy="order_good")
     */
    private $expenses;

    /**
     * @var ExpenseSklad[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Sklad\ExpenseSklad", cascade={"persist"}, orphanRemoval=true, mappedBy="order_good")
     */
    private $expense_sklads;

    /**
     * @var ZapCardReserve[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Reserve\ZapCardReserve", cascade={"persist"}, orphanRemoval=true, mappedBy="order_good")
     */
    private $zapCardReserve;

    /**
     * @var OrderAlert[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Alert\OrderAlert", cascade={"persist"}, orphanRemoval=true, mappedBy="good")
     */
    private $alerts;

    /**
     * @var SchetGood
     * @ORM\OneToOne(targetEntity="App\Model\Firm\Entity\SchetGood\SchetGood", mappedBy="order_good")
     */
    private $schet_good;

    public function __construct(Order $order, DetailNumber $number, Creater $creater, ?ZapSklad $zapSklad, ?ProviderPrice $providerPrice, Manager $manager, string $price, string $discount, int $quantity, int $isFromSite, ?ZapCardStock $stock, bool $no_discount)
    {
        $this->order = $order;
        $this->number = $number;
        $this->creater = $creater;
        $this->zapSklad = $zapSklad;
        $this->providerPrice = $providerPrice;
        $this->manager = $manager;
        $this->price = $price;
        $this->discount = $discount;
        $this->quantity = $quantity;
        $this->dateofadded = new DateTime();
        $this->isFromSite = $isFromSite;
        $this->stock = $stock;
        $this->no_discount = $no_discount;
        $this->lastIncomeStatusData = new LastIncomeStatusData();
        $this->lastIncomeStatus = null;
        $this->expenses = new ArrayCollection();
        $this->expense_sklads = new ArrayCollection();
        $this->zapCardReserve = new ArrayCollection();
        $this->alerts = new ArrayCollection();
        $this->number_old = new DetailNumber('');
    }

    /**
     * @param Order $order
     * @param ProviderPrice $providerPrice
     * @param DetailNumber $number
     * @param Creater $creater
     * @param PartPriceService $partPriceService
     * @param ZapCardStock|null $stock
     * @param Manager $manager
     * @param int $quantity
     * @return OrderGood
     * @throws Exception
     */
    public static function createFromProviderPrice(Order $order, ProviderPrice $providerPrice, DetailNumber $number, Creater $creater, PartPriceService $partPriceService, ?ZapCardStock $stock, Manager $manager, int $quantity): OrderGood
    {
        $user = $order->getUser();
        $discount = 0;

        $price = $partPriceService->onePriceClient($number, $creater, $providerPrice, $user->getOpt());

        if (!$stock) {
            $discount = $providerPrice->getDiscountParts($user->getDiscountParts());
        }

        return new self(
            $order,
            $number,
            $creater,
            null,
            $providerPrice,
            $manager,
            $price,
            $discount,
            $quantity,
            0,
            $stock,
            false
        );
    }

    /**
     * @param Order $order
     * @param ZapSklad $zapSklad
     * @param ZapCard $zapCard
     * @param ZapCardPriceService $zapCardPriceService
     * @param ZapCardStock|null $stock
     * @param Manager $manager
     * @param int $quantity
     * @return OrderGood
     */
    public static function createFromZapSklad(Order $order, ZapSklad $zapSklad, ZapCard $zapCard, ZapCardPriceService $zapCardPriceService, ?ZapCardStock $stock, Manager $manager, int $quantity): OrderGood
    {
        $user = $order->getUser();
        $discount = 0;
        $no_discount = false;

        $price = $zapCardPriceService->priceOpt($zapCard, $user->getOpt());
        if (!$stock) {
            $discount = $user->getDiscountParts();
        }

        if ($zapCard->getPriceGroup()) {
            $no_discount = $zapCard->getPriceGroup()->getPriceList()->getNoDiscount();
        }

        if ($no_discount) $discount = 0;

        return new self(
            $order,
            $zapCard->getNumber(),
            $zapCard->getCreater(),
            $zapSklad,
            null,
            $manager,
            $price,
            $discount,
            $quantity,
            0,
            $stock,
            $no_discount
        );
    }

    public static function cloneFromOrderGood(OrderGood $clone, int $quantity, Income $income = null): OrderGood
    {
        $orderGood = clone $clone;
        $orderGood->goodID = null;
        $orderGood->income = $income;
        $orderGood->quantity = $quantity;
        $orderGood->lastIncomeStatusData = new LastIncomeStatusData();
        $orderGood->lastIncomeStatus = null;
        $orderGood->dateofadded = new DateTime();
        return $orderGood;
    }

    public static function cloneFromReturn(OrderGood $clone, IncomeDocument $incomeDocument, ZapSklad $zapSklad, int $quantity, Manager $manager, float $price, string $return_reason): OrderGood
    {
        $orderGood = new self(
            $clone->getOrder(), $clone->getNumber(), $clone->getCreater(), $zapSklad, null, $manager, $price, 0, $quantity, 0, null, false
        );
        $orderGood->incomeDocument = $incomeDocument;
        $orderGood->income = null;
        $orderGood->priceZak = $price;
        $orderGood->dateofdeleted = new DateTime();
        $orderGood->isDeleted = true;
        $orderGood->returning_reason = $return_reason;
        return $orderGood;
    }

    public function updateLastIncomeStatus(IncomeStatus $status)
    {
        $this->lastIncomeStatus = $status;
        $this->lastIncomeStatusData = new LastIncomeStatusData(false);
    }

    public function updateProviderPrice(ProviderPrice $providerPrice)
    {
        $this->providerPrice = $providerPrice;
        $this->zapSklad = null;
    }

    public function changeNumbers(DetailNumber $number)
    {
        if ($this->number_old->getValue() == '') {
            $this->number_old = $this->number;
        }
        $this->number = $number;
    }

    public function deleteGood(?DeleteReason $deleteReason, ?Manager $deleteManager = null)
    {
        $this->dateofdeleted = new DateTime();
        $this->isDeleted = true;
        $this->deleteReason = $deleteReason;
        $this->deleteReasonEmailed = false;
        $this->deleteManager = $deleteManager;
    }

    public function splitQuantity(int $quantity, int $quantity_new): OrderGood
    {
        $this->quantity = $quantity;
        $orderGoodNew = OrderGood::cloneFromOrderGood($this, $quantity_new);
        $this->order->assignOrderGood($orderGoodNew);
        return $orderGoodNew;
    }

    public function updateQuantity(int $quantity)
    {
        $this->quantity = $quantity;
    }

    public function updateQuantityPicking(int $quantityPicking)
    {
        $this->quantityPicking = $quantityPicking;
    }

    public function updateZapSklad(ZapSklad $zapSklad)
    {
        $this->zapSklad = $zapSklad;
        $this->providerPrice = null;
        $this->income = null;
    }

    public function updatePrice(string $price, string $discount)
    {
        $this->price = $price;
        $this->discount = $discount;
    }

    public function updateIncome(?Income $income)
    {
        $this->income = $income;
    }

    public function updateSchet(Schet $schet)
    {
        $this->schet = $schet;
    }

    public function updateExpenseDocument(ExpenseDocument $expenseDocument, Manager $manager)
    {
        $this->expenseDocument = $expenseDocument;
        $this->expenseManager = $manager;
    }

    public function removeSchet()
    {
        $this->schet = null;
    }

    public function increaseQuantityReturn(int $quantityReturn)
    {
        $this->quantityReturn += $quantityReturn;
    }

    public function increaseQuantityPickingMaximumAllowing(int $quantity): int
    {
        if ($quantity > $this->quantity - $this->quantityPicking) {
            $quantityPicking = $this->quantity - $this->quantityPicking;
        } else {
            $quantityPicking = $quantity;
        }
        $this->increaseQuantityPicking($quantityPicking);
        return $quantity - $quantityPicking;
    }

    public function increaseQuantityPicking(int $quantityPicking)
    {
        $this->quantityPicking += $quantityPicking;
    }

    public function unPicking()
    {
        $this->quantityPicking = 0;
    }

    public function clearAllEmailed()
    {
        $this->lastIncomeStatusData = new LastIncomeStatusData(true);
        $this->deleteReasonEmailed = true;
    }

    public function deleteReasonWasEmailed(): void
    {
        $this->deleteReasonEmailed = true;
    }

    public function assignAlert(OrderAlertType $type): void
    {
        $this->alerts->add(new OrderAlert($type, $this));
    }

    /**
     * @param Income[] $incomes
     * @param Manager $manager
     * @param bool $isExpense
     * @param bool $isPerem
     * @param ZapSklad|null $zapSklad_to
     * @return void
     */
    public function reserve(array $incomes, Manager $manager, bool $isExpense = false, bool $isPerem = false, ?ZapSklad $zapSklad_to = null)
    {
        $quantityReserved = $this->getQuantityReserved();
        $quantity = $this->quantity;

        if ($isPerem || $isExpense) {
            foreach ($this->zapCardReserve as $zapCardReserve) {
                $zapCardReserve->updateDateOfClosed();
                if ($isPerem) {
                    $this->assignExpenseSklad($zapCardReserve->getIncome()->getZapCard(), $zapCardReserve->getZapSklad(), $zapSklad_to, $zapCardReserve->getIncome(), $zapCardReserve->getQuantity());
                }
                if ($isExpense) {
                    $this->assignExpense($zapCardReserve->getIncome(), $zapCardReserve->getQuantity());
                }
            }
        }

        $quantity -= $quantityReserved;

        if ($quantity > 0) {
            foreach ($incomes as $income) {
                // Получаем единственный склад из выборки
                $incomeSklad = $income->getSkladByZapSklad($this->getZapSklad());
                $quantityInWareHouse = $incomeSklad->getQuantityInWarehouse();
                if ($quantityInWareHouse >= $quantity) {
                    $quantityInWareHouse = $quantity;
                    $quantity = 0;
                } else {
                    $quantity -= $quantityInWareHouse;
                }

                if (!$isPerem && !$isExpense) {
                    $dateofclosed = new DateTime('+3 day');
                } else {
                    $dateofclosed = null;
                }

                $this->addZapCardReserve($income, $incomeSklad, $quantityInWareHouse, $manager, $dateofclosed);

                // Добавить расход по складу
                if ($isPerem) {
                    $this->assignExpenseSklad($income->getZapCard(), $incomeSklad->getZapSklad(), $zapSklad_to, $income, $quantityInWareHouse);
                }

                if ($isExpense) {
                    $this->assignExpense($income, $quantityInWareHouse);
                }

                if ($quantity == 0) break;
            }
        }
    }

    public function assignExpenseSklad(ZapCard $zapCard, ZapSklad $zapSklad, ZapSklad $zapSklad_to, Income $income, int $quantity)
    {
        $expenseSklad = new ExpenseSklad($zapCard, $zapSklad, $zapSklad_to, $income, $quantity, $this);
        $this->expense_sklads->add($expenseSklad);
    }

    public function assignExpense(Income $income, int $quantity)
    {
        $expense = new Expense($this, $income, $quantity);
        $this->expenses->add($expense);
    }

    public function addZapCardReserve(Income $income, IncomeSklad $incomeSklad, int $quantity, Manager $manager, ?DateTime $dateofclosed)
    {
        $income->changeReserve($quantity);
        $incomeSklad->changeReserve($quantity);
        $this->assignZapCardReserve($incomeSklad->getZapSklad(), $income, $quantity, $dateofclosed, $manager);
    }

    public function assignZapCardReserve(ZapSklad $zapSklad, Income $income, int $quantity, ?Datetime $dateofclosed, Manager $manager)
    {
        $zapCardReserve = new ZapCardReserve(
            $zapSklad, $income, $income->getZapCard()->getNumber(), $quantity, $dateofclosed, $this->getOrder(), $this, $manager
        );
        $this->zapCardReserve->add($zapCardReserve);
    }

    public function shipBetweenSklads(ExpenseSklad $expense)
    {
        $reserves = $this->getZapCardReserveByIncome($expense->getIncome());
        foreach ($reserves as $reserve) {
            $reserve->getIncome()->shipBetweenSklads($expense->getZapSklad(), $expense->getZapSkladTo(), $reserve->getQuantity());
            $reserve->updateZapSklad($expense->getZapSkladTo());
            $reserve->updateDateOfClosed();
        }

        if (!$this->income) {
            $this->updateZapSklad($expense->getZapSkladTo());
        }
    }

    public function shippedOnSklad(ExpenseSklad $expense)
    {
        $expense->getIncome()->fromPathToSklad($expense->getZapSkladTo(), $expense->getQuantity());
        $expense->getZapCard()->assignLocation($expense->getZapSkladTo());

        if ($this->zapSklad) {
            $date3DaysLater = new \DateTime('+3 day');
            foreach ($this->zapCardReserve as $zapCardReserve) {
                $zapCardReserve->updateDateOfClosed($date3DaysLater);
            }
        }
    }

    public function removeReserve()
    {
        foreach ($this->zapCardReserve as $zapCardReserve) {
            $this->removeZapCardReserve($zapCardReserve);
        }
    }

    public function removeZapCardReserve(ZapCardReserve $zapCardReserve)
    {
        $zapCardReserve->getIncome()->changeReserve(-$zapCardReserve->getQuantity());
        $incomeSklad = $zapCardReserve->getIncome()->getSkladByZapSklad($zapCardReserve->getZapSklad());
        $incomeSklad->changeReserve(-$zapCardReserve->getQuantity());
        $this->zapCardReserve->removeElement($zapCardReserve);
    }

    public function clearZapCardReserve()
    {
        $this->zapCardReserve->clear();
    }

    public function clearExpenses()
    {
        $this->expenses->clear();
    }

    public function getId(): int
    {
        return $this->goodID;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getExpenseDocument(): ?ExpenseDocument
    {
        return $this->expenseDocument;
    }

    public function getExpenseManager(): ?Manager
    {
        return $this->expenseManager;
    }

    public function getIncomeDocument(): ?IncomeDocument
    {
        return $this->incomeDocument;
    }

    public function getSchet(): ?Schet
    {
        return $this->schet;
    }

    public function getNumber(): DetailNumber
    {
        return $this->number;
    }

    public function getNumberOld(): DetailNumber
    {
        return $this->number_old;
    }

    public function getProviderPrice(): ?ProviderPrice
    {
        return $this->providerPrice;
    }

    public function getZapSklad(): ?ZapSklad
    {
        return $this->zapSklad;
    }

    public function getCreater(): ?Creater
    {
        return $this->creater;
    }

    public function getIncome(): ?Income
    {
        return $this->income;
    }

    public function removeIncome(): void
    {
        $this->income = null;
    }

    public function getStock(): ?ZapCardStock
    {
        return $this->stock;
    }

    public function getNoDiscount(): bool
    {
        return $this->no_discount;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getDateofdeleted(): ?\DateTimeInterface
    {
        return $this->dateofdeleted;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function getDeleteReason(): ?DeleteReason
    {
        return $this->deleteReason;
    }

    public function getDeleteReasonEmailed(): bool
    {
        return $this->deleteReasonEmailed;
    }

    public function getDeleteManager(): ?Manager
    {
        return $this->deleteManager;
    }

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function getPriceZak(): string
    {
        return $this->priceZak;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function getDiscount(): string
    {
        return $this->discount;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getQuantityReturn(): int
    {
        return $this->quantityReturn;
    }

    public function getQuantityPicking(): int
    {
        return $this->quantityPicking;
    }

    public function getPage(): string
    {
        return $this->page;
    }

    public function getReturningReason(): string
    {
        return $this->returning_reason;
    }

    public function isHideNumber(): bool
    {
        return $this->isHideNumber;
    }

    public function getIsFromSite(): int
    {
        return $this->isFromSite;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getLastIncomeStatusData(): LastIncomeStatusData
    {
        return $this->lastIncomeStatusData;
    }

    /**
     * @return IncomeStatus
     */
    public function getLastIncomeStatus(): IncomeStatus
    {
        return $this->lastIncomeStatus;
    }

    /**
     * @return ZapCardReserve[]|ArrayCollection
     */
    public function getZapCardReserve()
    {
        return $this->zapCardReserve->toArray();
    }

    /**
     * @return ZapCardReserve[]
     */
    public function getZapCardReserveByIncome(Income $income): array
    {
        $result = [];
        foreach ($this->zapCardReserve as $reserve) {
            if ($reserve->getIncome()->getId() == $income->getId()) {
                $result[] = $reserve;
            }
        }
        return $result;
    }

    public function getQuantityReserved(): int
    {
        $quantity = 0;
        foreach ($this->zapCardReserve as $zapCardReserve) {
            $quantity += $zapCardReserve->getQuantity();
        }
        return $quantity;
    }

    public function getDiscountPrice(): int
    {
        return round($this->price - $this->price * $this->discount / 100);
    }

    /**
     * @return Expense[]|ArrayCollection
     */
    public function getExpenses()
    {
        return $this->expenses->toArray();
    }

    /**
     * @return ExpenseSklad[]|ArrayCollection
     */
    public function getExpenseSklads()
    {
        return $this->expense_sklads->toArray();
    }

    /**
     * @return ExpenseSklad[]
     */
    public function getExpenseSkladsNotIncome(): array
    {
        $expenses = [];
        foreach ($this->expense_sklads as $expense) {
            if ($expense->getStatus() != ExpenseSklad::INCOME) $expenses[] = $expense;
        }
        return $expenses;
    }

    /**
     * @return OrderAlert[]|ArrayCollection
     */
    public function getAlerts()
    {
        return $this->alerts;
    }

}
