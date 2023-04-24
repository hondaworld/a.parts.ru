<?php

namespace App\Model\Income\Entity\Income;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Reserve\ZapCardReserve;
use App\Model\Card\Entity\ReserveSklad\ZapCardReserveSklad;
use App\Model\Expense\Entity\Expense\Expense;
use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Income\Entity\Document\IncomeDocument;
use App\Model\Income\Entity\Good\IncomeGood;
use App\Model\Income\Entity\Order\IncomeOrder;
use App\Model\Income\Entity\Sklad\IncomeSklad;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Income\Entity\StatusHistory\IncomeStatusHistory;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\AlertType\OrderAlertType;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Order\Entity\Order\Order;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Shop\Entity\DeleteReason\DeleteReason;
use App\Model\Shop\Entity\Gtd\ShopGtd;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IncomeRepository::class)
 * @ORM\Table(name="income")
 */
class Income
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="incomeID")
     */
    private $incomeID;

    /**
     * @var IncomeOrder
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Order\IncomeOrder", inversedBy="incomes")
     * @ORM\JoinColumn(name="incomeOrderID", referencedColumnName="incomeOrderID", nullable=true)
     */
    private $income_order;

    /**
     * @var IncomeDocument
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Document\IncomeDocument", inversedBy="incomes")
     * @ORM\JoinColumn(name="incomeDocumentID", referencedColumnName="incomeDocumentID", nullable=true)
     */
    private $income_document;

    /**
     * @var ProviderPrice
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\Price\ProviderPrice", inversedBy="incomes")
     * @ORM\JoinColumn(name="providerPriceID", referencedColumnName="providerPriceID", nullable=true)
     */
    private $provider_price;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="incomes")
     * @ORM\JoinColumn(name="firmID", referencedColumnName="firmID", nullable=true)
     */
    private $firm;

    /**
     * @var ShopGtd
     * @ORM\ManyToOne(targetEntity="App\Model\Shop\Entity\Gtd\ShopGtd", inversedBy="incomes")
     * @ORM\JoinColumn(name="shop_gtdID", referencedColumnName="shop_gtdID", nullable=true)
     */
    private $shop_gtd;

    /**
     * @var ShopGtd
     * @ORM\ManyToOne(targetEntity="App\Model\Shop\Entity\Gtd\ShopGtd", inversedBy="incomes1")
     * @ORM\JoinColumn(name="shop_gtdID1", referencedColumnName="shop_gtdID", nullable=true)
     */
    private $shop_gtd1;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @var IncomeStatus
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Status\IncomeStatus", inversedBy="incomes", fetch="EAGER")
     * @ORM\JoinColumn(name="status", referencedColumnName="status", nullable=false)
     */
    private $status;

    /**
     * @var ZapCard
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Card\ZapCard", inversedBy="incomes", fetch="EAGER")
     * @ORM\JoinColumn(name="zapCardID", referencedColumnName="zapCardID", nullable=false)
     */
    private $zapCard;

    /**
     * @ORM\Column(type="integer", name="quantityIn")
     */
    private $quantityIn = 0;

    /**
     * @ORM\Column(type="integer", name="quantityPath")
     */
    private $quantityPath = 0;

    /**
     * @ORM\Column(type="integer", name="quantity")
     */
    private $quantity = 0;

    /**
     * @ORM\Column(type="integer", name="quantityUnPack")
     */
    private $quantityUnPack = 0;

    /**
     * @ORM\Column(type="integer", name="reserve")
     */
    private $reserve = 0;

    /**
     * @ORM\Column(type="koef", precision=7, scale=4, name="priceZak")
     */
    private $priceZak = 0;

    /**
     * @ORM\Column(type="koef", precision=7, scale=4, name="priceDost")
     */
    private $priceDost = 0;

    /**
     * @ORM\Column(type="koef", precision=7, scale=4, name="price")
     */
    private $price = 0;

    /**
     * @ORM\Column(type="koef", precision=7, scale=4, name="priceScan")
     */
    private $priceScan = -1;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateofzakaz;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateofin;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateofout;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateofinplan;

    /**
     * @ORM\Column(type="text")
     */
    private $returning_reason = '';

    /**
     * @ORM\Column(type="integer", name="quantityReturn")
     */
    private $quantityReturn = 0;

    /**
     * @var DeleteReason
     * @ORM\ManyToOne(targetEntity="App\Model\Shop\Entity\DeleteReason\DeleteReason", inversedBy="incomes")
     * @ORM\JoinColumn(name="deleteReasonID", referencedColumnName="deleteReasonID", nullable=true)
     */
    private $deleteReason;

    /**
     * @ORM\Column(type="boolean", name="isSummDone")
     */
    private $isSummDone = false;

    /**
     * @var IncomeSklad[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Sklad\IncomeSklad", cascade={"persist"}, mappedBy="income", orphanRemoval=true)
     */
    private $sklads;

    /**
     * @var IncomeStatusHistory[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\StatusHistory\IncomeStatusHistory", cascade={"persist"}, mappedBy="income")
     */
    private $income_status_history;

    /**
     * @var OrderGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Good\OrderGood", cascade={"persist"}, mappedBy="income")
     */
    private $order_goods;

    /**
     * @var Expense[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Expense\Expense", mappedBy="income")
     */
    private $expenses;

    /**
     * @var ExpenseSklad[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Sklad\ExpenseSklad", cascade={"persist"}, orphanRemoval=true, mappedBy="income")
     */
    private $expense_sklads;

    /**
     * @var ZapCardReserve[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Reserve\ZapCardReserve", cascade={"persist"}, mappedBy="income", orphanRemoval=true)
     */
    private $zapCardReserve;

    /**
     * @var ZapCardReserveSklad[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\ReserveSklad\ZapCardReserveSklad", cascade={"persist"}, mappedBy="income", orphanRemoval=true)
     */
    private $zapCardReserveSklad;

    /**
     * @var IncomeGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Good\IncomeGood", mappedBy="income")
     */
    private $income_goods;

    public function __construct(?ProviderPrice $providerPrice, IncomeStatus $status, ZapCard $zapCard, int $quantity, float $priceZak, float $priceDost, float $price)
    {
        $this->provider_price = $providerPrice;
        $this->dateofadded = new \DateTime();
        $this->status = $status;
        $this->zapCard = $zapCard;
        $this->quantity = $quantity;
        $this->priceZak = $priceZak;
        $this->priceDost = $priceDost;
        $this->price = $price;
        $this->sklads = new ArrayCollection();
        $this->income_status_history = new ArrayCollection();
        $this->order_goods = new ArrayCollection();
        $this->expenses = new ArrayCollection();
        $this->expense_sklads = new ArrayCollection();
        $this->zapCardReserve = new ArrayCollection();
        $this->zapCardReserveSklad = new ArrayCollection();
        $this->income_goods = new ArrayCollection();
    }

    public static function cloneFromIncome(Income $clone, int $quantity): Income
    {
        $income = clone $clone;
        $income->incomeID = null;
        $income->sklads = new ArrayCollection();
        $income->income_status_history = new ArrayCollection();
        $income->order_goods = new ArrayCollection();
        $income->expenses = new ArrayCollection();
        $income->expense_sklads = new ArrayCollection();
        $income->zapCardReserve = new ArrayCollection();
        $income->zapCardReserveSklad = new ArrayCollection();
        $income->income_goods = new ArrayCollection();
        $income->changeQuantity($quantity);
        return $income;
    }

    public function updateZapCard(ZapCard $zapCard)
    {
        if ($this->getZapCard()->getNumber()->isEqual($zapCard->getNumber())) {
            throw new \DomainException('Номер не изменен');
        }

        if ($this->getStatus()->isInWarehouse() || $this->getStatus()->isDeleted()) {
            throw new \DomainException('Приход не должен быть удален и не должен быть на складе');
        }
        if ($this->getReserve() > 0) {
            throw new \DomainException('Не должно быть резерва');
        }

        $this->zapCard = $zapCard;
        foreach ($this->order_goods as $orderGood) {
            $orderGood->changeNumbers($zapCard->getNumber());
        }
    }

    public function updateProviderPrice(ProviderPrice $providerPrice)
    {
        $this->provider_price = $providerPrice;

        foreach ($this->order_goods as $orderGood) {
            $orderGood->updateProviderPrice($providerPrice);
        }
    }

    public function updatePrices(float $priceZak, float $priceDost, float $price)
    {
        $this->priceZak = $priceZak;
        $this->priceDost = $priceDost;
        $this->price = $price;
    }

    public function updateGtd(?ShopGtd $shopGtd)
    {
        $this->shop_gtd = $shopGtd;
    }

    public function updateDateOfZakaz(?\DateTime $dateofzakaz)
    {
        $this->dateofzakaz = $dateofzakaz;
    }

    public function updateDateOfIn(?\DateTime $dateofin)
    {
        $this->dateofin = $dateofin;
    }

    public function updateDateOfInPlan(?\DateTime $dateofinplan)
    {
        $this->dateofinplan = $dateofinplan;
    }

    public function updateDateOfOut(?\DateTime $dateofout)
    {
        $this->dateofout = $dateofout;
    }

    public function increaseOrderQuantity(int $quantity)
    {
        $this->quantity += $quantity;
    }

    public function updateQuantity(int $quantity, int $quantityIn, int $quantityPath, int $reserve, int $quantityReturn)
    {
        $this->quantity = $quantity;
        $this->quantityIn = $quantityIn;
        $this->quantityPath = $quantityPath;
        $this->reserve = $reserve;
        $this->quantityReturn = $quantityReturn;
    }

    public function changeIncomeQuantity(int $quantity): void
    {
        if ($this->isOrderIncome()) {
            $orderGood = $this->getFirstOrderGood();
            if ($orderGood->getQuantity() < $quantity) {
                throw new \DomainException('Нельзя увеличить количество в заказной детали');
            }
            if ($orderGood->getQuantity() > $quantity) {
                $orderGood->getOrder()->assignOrderGood(OrderGood::cloneFromOrderGood($orderGood, $orderGood->getQuantity() - $quantity));
                $orderGood->updateQuantity($quantity);
            }
        }
        $this->changeQuantity($quantity);
    }

    public function splitIncomeQuantity(int $quantity, Income $incomeNew): void
    {
        $quantity_new = $incomeNew->getQuantity();
        $this->changeQuantity($quantity);

        $incomeSklad = $this->getFirstSklad();
        if ($incomeSklad) {
            $incomeNew->assignSklad(IncomeSklad::cloneFromIncomeSklad($incomeSklad, $incomeNew, $quantity_new));
        }

        if ($this->isOrderIncome()) {
            $orderGood = $this->getFirstOrderGood();
            $orderGood->updateQuantity($quantity);

            $orderGoodNew = OrderGood::cloneFromOrderGood($orderGood, $quantity_new, $incomeNew);
            $incomeNew->assignOrderGood($orderGoodNew);
//            $orderGood->getOrder()->assignOrderGood($orderGoodNew);

            foreach ($this->zapCardReserve as $zapCardReserve) {
                $incomeNew->assignZapCardReserve($zapCardReserve->getZapSklad(), $zapCardReserve->getNumber(), $quantity_new, $zapCardReserve->getDateofclosed(), $orderGoodNew->getOrder(), $orderGoodNew, $zapCardReserve->getManager());
            }
        }
    }

    public function changeQuantity(int $quantity)
    {
        if ($this->sklads->count() > 1) throw new \DomainException('У прихода больше одного склада');
        if ($this->status->isDeleted() || $this->status->isInWarehouse()) throw new \DomainException('Деталь удалена или уже на складе');
        $this->quantity = $quantity;
        if ($this->quantityPath > 0) $this->quantityPath = $quantity;
        if ($this->reserve > 0) $this->reserve = $quantity;

        $incomeSklad = $this->getFirstSklad();
        if ($incomeSklad) {
            $incomeSklad->changeQuantity($quantity);
        }

        foreach ($this->zapCardReserve as $zapCardReserve) {
            $zapCardReserve->updateQuantity($quantity);
        }
    }

    public function assignZapCardReserve(ZapSklad $zapSklad, DetailNumber $number, int $quantity, ?\DateTime $dateofclosed, Order $order, OrderGood $orderGood, Manager $manager): void
    {
        $this->zapCardReserve->add(new ZapCardReserve($zapSklad, $this, $number, $quantity, $dateofclosed, $order, $orderGood, $manager));
    }

    public function assignOrderGood(OrderGood $orderGood): void
    {
        $this->order_goods->add($orderGood);
    }

    public function updateDatesForChangeStatus(IncomeStatus $status, \DateTime $now, ?\DateTime $dateofinplan): void
    {
        if (!$status->isDeleted()) {
            if (!$this->getDateofzakaz()) {
                $this->dateofzakaz = $now;
            }
            if ($status->isOnTheWayOrInIncomingOnWarehouse()) {
                if (!$this->getDateofout()) {
                    $this->dateofout = $now;
                }
                if ($dateofinplan) {
                    $this->dateofinplan = $dateofinplan;
                } elseif (!$this->getDateofinplan()) {
                    $this->dateofinplan = $now;
                }
            }
        }
    }

    public function getOneSkladOrCreate(?ZapSklad $zapSklad = null, ?int $quantity = null): IncomeSklad
    {
        if (!$zapSklad) {
            $incomeSklad = $this->getFirstSklad();
        } else {
            $incomeSklad = $this->getSkladByZapSklad($zapSklad);
        }

        if (!$incomeSklad) {
            if (!$zapSklad) {
                $zapSklad = $this->getProviderPrice()->getProvider()->getZapSklad();
            }
            $incomeSklad = new IncomeSklad($this, $zapSklad, $quantity !== null ? $quantity : $this->getQuantity());
            $this->assignSklad($incomeSklad);
        }

        return $incomeSklad;
    }

    public function shipping(IncomeSklad $incomeSklad, Manager $manager)
    {
        $this->setQuantityPathAsQuantity();
        $incomeSklad->setQuantityPathAsQuantity();

        $this->getZapCard()->restore();

        if ($this->isOrderIncome()) {
            $this->addReserveByOrderGood($manager);
        }
    }

    public function addReserveByOrderGood(Manager $manager): void
    {
        $incomeSklad = $this->getFirstSklad();
        if ($this->zapCardReserve->count() == 0 && $incomeSklad) {
            $orderGood = $this->getFirstOrderGood();

            $this->reserveOrderGoodQuantity($orderGood);
            $incomeSklad->reserveOrderGoodQuantity($orderGood);

            $this->assignZapCardReserve($incomeSklad->getZapSklad(), $this->getZapCard()->getNumber(), $orderGood->getQuantity(), null, $orderGood->getOrder(), $orderGood, $manager);
        }
    }

    public function addReserveByZapSklad(IncomeSklad $incomeSklad, ZapSklad $zapSklad_to, int $quantity, Manager $manager): void
    {
        // Добавить расход по складу
        $expenseSklad = $this->assignExpenseSkladWithoutOrderGood($this->zapCard, $incomeSklad->getZapSklad(), $zapSklad_to, $quantity);

        // Добавить резервы
        $this->changeReserve($quantity);
        $incomeSklad->changeReserve($quantity);

        $expenseSklad->assignZapCardReserveSklad($this->zapCard, $incomeSklad->getZapSklad(), $zapSklad_to, $this, $quantity, $manager);
    }

    public function assignExpenseSkladWithoutOrderGood(ZapCard $zapCard, ZapSklad $zapSklad, ZapSklad $zapSklad_to, int $quantity): ExpenseSklad
    {
        $expenseSklad = new ExpenseSklad($zapCard, $zapSklad, $zapSklad_to, $this, $quantity);
        $this->expense_sklads->add($expenseSklad);
        return $expenseSklad;
    }

    public function shipBetweenSklads(ZapSklad $zapSklad, ZapSklad $zapSklad_to, int $quantity)
    {
        $incomeSklad = $this->getSkladByZapSklad($zapSklad);

        // Убрать резервы с текущего склада
        $incomeSklad->changeReserve(-$quantity);

        // Убрать со склада
        $this->fromInToPath($quantity);
        $incomeSklad->fromInToPath($quantity);
//        dump($incomeSklad);

        // Переместить на новый склад
        $incomeSkladTo = $this->getOneSkladOrCreate($zapSklad_to, 0);
        $incomeSkladTo->sendToPath($quantity);
        $incomeSkladTo->changeReserve($quantity);
    }

    public function returning(IncomeSklad $incomeSklad)
    {
        $this->removeQuantityPath();
        $incomeSklad->removeQuantityPath();

        if ($this->isOrderIncome()) {
            $this->removeReserveByOrderGood();
        }
    }

    public function rejecting(IncomeSklad $incomeSklad, ?DeleteReason $deleteReason, OrderAlertType $orderAlertType)
    {
        $this->removeQuantityPath();
        $incomeSklad->removeQuantityPath();
        $this->updateDeleteReason($deleteReason);

        if ($this->isOrderIncome()) {
            $this->removeReserveByOrderGood();
            $orderGood = $this->getFirstOrderGood();
            $orderGood->deleteGood($deleteReason);
            $orderGood->assignAlert($orderAlertType);
        }
    }

    public function removeReserveByOrderGood(): void
    {
        $incomeSklad = $this->getFirstSklad();

        $this->removeReserve();
        if ($incomeSklad) $incomeSklad->removeReserve();
        $this->zapCardReserve->clear();
    }

    public function removeReserve()
    {
        $this->reserve = 0;
    }

    public function updateDeleteReason(?DeleteReason $deleteReason)
    {
        $this->deleteReason = $deleteReason;
    }

    /**
     * @return IncomeSklad[]|ArrayCollection
     */
    public function getSklads()
    {
        return $this->sklads->toArray();
    }

    public function clearSklads(): void
    {
        $this->sklads->clear();
    }

    public function changeSklad(ZapSklad $zapSklad): void
    {
        if ($this->sklads->count() != 1) {
            throw new \DomainException('У детали ' . $this->getZapCard()->getNumber()->getValue() . ' склад должен быть один');
        }

        $incomeSklad = $this->getFirstSklad();

        if ($incomeSklad->getZapSklad()->getId() == $zapSklad->getId()) {
            throw new \DomainException('У детали ' . $this->getZapCard()->getNumber()->getValue() . ' склад уже указанный');
        }

        foreach ($this->zapCardReserve as $zapCardReserve) {
            if ($zapCardReserve->getZapSklad()->getId() == $incomeSklad->getZapSklad()->getId()) {
                $zapCardReserve->updateZapSklad($zapSklad);
            }
        }
        $incomeSklad->updateZapSklad($zapSklad);
    }

    public function assignSklad(IncomeSklad $incomeSklad): void
    {
        $this->sklads->add($incomeSklad);
    }

    /**
     * @return IncomeSklad|null
     */
    public function getFirstSklad(): ?IncomeSklad
    {
        if ($this->sklads->count() == 1) return $this->sklads[0];
        if ($this->sklads->count() > 1) {
            foreach ($this->sklads as $sklad) {
                if ($sklad->getQuantity() > 0) return $sklad;
            }
            return $this->sklads[0];
        }
        return null;
    }

    /**
     * @param ZapSklad $zapSklad
     * @return IncomeSklad|null
     */
    public function getSkladByZapSklad(ZapSklad $zapSklad): ?IncomeSklad
    {
        foreach ($this->sklads as $sklad) {
            if ($sklad->getZapSklad()->getId() == $zapSklad->getId()) return $sklad;
        }
        return null;
    }

    /**
     * @return IncomeSklad|null
     */
    public function getSkladWithPositiveQuantity(): ?IncomeSklad
    {
        foreach ($this->sklads as $sklad) {
            if ($sklad->getQuantity() > 0) return $sklad;
        }
        return null;
    }

    public function returnQuantity(int $quantity)
    {
        if ($this->quantityIn - $this->reserve < $quantity) {
            throw new \DomainException('Списываемое количество больше доступного');
        }
        $this->quantityReturn += $quantity;
        $this->quantityIn -= $quantity;
    }

    public function setQuantityPathAsQuantity()
    {
        $this->quantityPath = $this->quantity;
    }

    public function updateStatus(IncomeStatus $incomeStatus, Manager $manager = null)
    {
        $this->status = $incomeStatus;
        if ($manager) {
            $this->income_status_history->add(new IncomeStatusHistory($incomeStatus, $this, $manager));
        }
        if ($this->isOrderIncome()) {
            $this->getFirstOrderGood()->updateLastIncomeStatus($incomeStatus);
        }
    }

    public function removeQuantityPath()
    {
        $this->quantityPath = 0;
    }

    public function reserveOrderGoodQuantity(OrderGood $orderGood)
    {
        $this->reserve = $orderGood->getQuantity();
    }

    public function incomeInWarehouse(IncomeDocument $incomeDocument, Firm $firm)
    {
        $this->dateofin = new \DateTime();
        $this->quantityIn = $this->quantity;
        $this->quantityPath = 0;
        $this->income_document = $incomeDocument;
        $this->firm = $firm;
    }

    public function addQuantityUnPack(int $quantityUnPack)
    {
        if ($quantityUnPack > $this->quantity - $this->quantityUnPack) {
            throw new \DomainException('В приходе нет такого количества');
        }
        $this->quantityUnPack += $quantityUnPack;
    }

    public function removeQuantityUnPack()
    {
        $this->quantityUnPack = 0;
    }

    public function updateIncomeOrder(IncomeOrder $incomeOrder)
    {
        $this->income_order = $incomeOrder;
    }

    public function removeIncomeOrder()
    {
        $this->income_order = null;
    }

    public function changeReserve(int $quantity): void
    {
        $this->reserve += $quantity;
    }

    public function expense(int $quantity): void
    {
        $this->reserve -= $quantity;
        $this->quantityIn -= $quantity;
    }

    public function fromInToPath(int $quantity): void
    {
        $this->quantityIn -= $quantity;
        $this->quantityPath += $quantity;
    }

    public function fromPathToSklad(ZapSklad $zapSklad, int $quantity)
    {
        $incomeSklad = $this->getSkladByZapSklad($zapSklad);
        $this->fromPathToIn($quantity);
        $incomeSklad->fromPathToIn($quantity);
    }

    public function fromPathToIn(int $quantity): void
    {
        $this->quantityIn += $quantity;
        $this->quantityPath -= $quantity;
    }

    public function getId(): ?int
    {
        return $this->incomeID;
    }

    public function getDateofzakaz(): ?\DateTime
    {
        if ($this->dateofzakaz && $this->dateofzakaz->format('Y') == '-0001') return null;
        return $this->dateofzakaz;
    }

    public function getDateofin(): ?\DateTime
    {
        if ($this->dateofin && $this->dateofin->format('Y') == '-0001') return null;
        return $this->dateofin;
    }

    public function getDateofout(): ?\DateTime
    {
        if ($this->dateofout && $this->dateofout->format('Y') == '-0001') return null;
        return $this->dateofout;
    }

    public function getDateofinplan(): ?\DateTime
    {
        if ($this->dateofinplan && $this->dateofinplan->format('Y') == '-0001') return null;
        return $this->dateofinplan;
    }

    /**
     * @return IncomeDocument
     */
    public function getIncomeDocument(): ?IncomeDocument
    {
        return $this->income_document;
    }

    /**
     * @return ProviderPrice
     */
    public function getProviderPrice(): ?ProviderPrice
    {
        return $this->provider_price;
    }

    /**
     * @return Firm
     */
    public function getFirm(): ?Firm
    {
        return $this->firm;
    }

    /**
     * @return ShopGtd
     */
    public function getShopGtd(): ?ShopGtd
    {
        return $this->shop_gtd;
    }

    /**
     * @return ShopGtd
     */
    public function getShopGtd1(): ?ShopGtd
    {
        return $this->shop_gtd1;
    }

    /**
     * @return \DateTime
     */
    public function getDateofadded(): \DateTime
    {
        return $this->dateofadded;
    }

    /**
     * @return IncomeStatus
     */
    public function getStatus(): IncomeStatus
    {
        return $this->status;
    }

    /**
     * @return ZapCard
     */
    public function getZapCard(): ZapCard
    {
        return $this->zapCard;
    }

    public function changeZapCardPrice(): void
    {
        $this->zapCard->updatePrice($this->getPrice(), $this->getPriceZak() + $this->getPriceDost(), $this->getProviderPrice(), $this->getProviderPrice()->getCurrency());
    }

    public function getSum(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * @return float
     */
    public function getPriceZak(): float
    {
        return $this->priceZak;
    }

    /**
     * @return float
     */
    public function getPriceDost(): float
    {
        return $this->priceDost;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * @return float
     */
    public function getPriceScan(): float
    {
        return $this->priceScan;
    }

    /**
     * @return int
     */
    public function getQuantityIn(): int
    {
        return $this->quantityIn;
    }

    /**
     * @return int
     */
    public function getQuantityPath(): int
    {
        return $this->quantityPath;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return int
     */
    public function getQuantityUnPack(): int
    {
        return $this->quantityUnPack;
    }

    /**
     * @return int
     */
    public function getReserve(): int
    {
        return $this->reserve;
    }

    /**
     * @return string
     */
    public function getReturningReason(): string
    {
        return $this->returning_reason;
    }

    /**
     * @return int
     */
    public function getQuantityReturn(): int
    {
        return $this->quantityReturn;
    }

    /**
     * @return DeleteReason
     */
    public function getDeleteReason(): ?DeleteReason
    {
        return $this->deleteReason;
    }

    /**
     * @return bool
     */
    public function isSummDone(): bool
    {
        return $this->isSummDone;
    }

    /**
     * @return IncomeOrder
     */
    public function getIncomeOrder(): IncomeOrder
    {
        return $this->income_order;
    }

    /**
     * @return IncomeStatusHistory[]|ArrayCollection
     */
    public function getIncomeStatusHistory()
    {
        return $this->income_status_history;
    }

    /**
     * @return OrderGood[]|ArrayCollection
     */
    public function getOrderGoods()
    {
        return $this->order_goods->toArray();
    }

    public function clearOrderGoods()
    {
        foreach ($this->order_goods as $order_good) {
            $order_good->removeIncome();
        }
    }

    public function isOrderIncome(): bool
    {
        return $this->order_goods->count() > 0;
    }

    public function getFirstOrderGood(): ?OrderGood
    {
        return $this->order_goods->count() > 0 ? $this->order_goods[0] : null;
    }

    /**
     * @return Expense[]|ArrayCollection
     */
    public function getExpenses()
    {
        return $this->expenses;
    }

    /**
     * @return ExpenseSklad[]|ArrayCollection
     */
    public function getExpenseSklads()
    {
        return $this->expense_sklads->toArray();
    }

    /**
     * @return ZapCardReserve[]|ArrayCollection
     */
    public function getZapCardReserve()
    {
        return $this->zapCardReserve->toArray();
    }

    public function removeZapCardReserveByOrderGood(OrderGood $orderGood)
    {
        foreach ($this->zapCardReserve as $zapCardReserve) {
            if ($zapCardReserve->getOrderGood()->getId() == $orderGood->getId()) {
                $this->zapCardReserve->removeElement($zapCardReserve);
            }
        }
    }

    /**
     * @return ZapCardReserveSklad[]|ArrayCollection
     */
    public function getZapCardReserveSklad()
    {
        return $this->zapCardReserveSklad->toArray();
    }

    public function getQuantityInWarehouse(): int
    {
        return $this->quantityIn + $this->quantityPath - $this->reserve;
    }


}
