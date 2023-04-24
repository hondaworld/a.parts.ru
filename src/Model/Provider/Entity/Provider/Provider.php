<?php

namespace App\Model\Provider\Entity\Provider;

use App\Model\Card\Entity\Stock\ZapCardStock;
use App\Model\Finance\Entity\Currency\Currency;
use App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory;
use App\Model\Income\Entity\Document\IncomeDocument;
use App\Model\Income\Entity\Order\IncomeOrder;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @ORM\Entity(repositoryClass=ProviderRepository::class)
 * @ORM\Table(name="providers")
 */
class Provider
{
    public const WEEKS = ['1' => 'ПН', '2' => 'ВТ', '3' => 'СР', '4' => 'ЧТ', '5' => 'ПТ', '6' => 'СБ', '7' => 'ВС'];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="providerID")
     */
    private $providerID;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User")
     * @ORM\JoinColumn(name="userID", referencedColumnName="userID", nullable=false)
     */
    private $user;

    /**
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad")
     * @ORM\JoinColumn(name="zapSkladID", referencedColumnName="zapSkladID", nullable=false)
     */
    private $zapSklad;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=2)
     */
    private $koef_dealer = 0;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="isDealer")
     */
    private $isDealer = false;

    /**
     * @ORM\Column(type="integer", name="incomeOrderNumber")
     */
    private $incomeOrderNumber = 1;

    /**
     * @ORM\Column(type="string", length=50, name="incomeOrderSubject")
     */
    private $incomeOrderSubject = '';

    /**
     * @ORM\Column(type="text", name="incomeOrderText")
     */
    private $incomeOrderText = '';

    /**
     * @ORM\Column(type="string", length=50, name="incomeOrderSubject5")
     */
    private $incomeOrderSubject5 = '';

    /**
     * @ORM\Column(type="text", name="incomeOrderText5")
     */
    private $incomeOrderText5 = '';

    /**
     * @ORM\Column(type="string", name="incomeOrderEmail")
     */
    private $incomeOrderEmail = '';

    /**
     * @ORM\Column(type="boolean", name="isIncomeOrder")
     */
    private $isIncomeOrder = false;

    /**
     * @ORM\Column(type="boolean", name="isIncomeOrderAutoSend")
     */
    private $isIncomeOrderAutoSend = false;

    /**
     * @ORM\Column(type="string", length=30, name="incomeOrderWeekDays")
     */
    private $incomeOrderWeekDays = '';

    /**
     * @ORM\Column(type="string", length=5, name="incomeOrderTime")
     */
    private $incomeOrderTime = '';

    /**
     * @var ProviderPrice[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Provider\Entity\Price\ProviderPrice", cascade={"persist"}, mappedBy="provider", orphanRemoval=true)
     */
    private $prices;

    /**
     * @var ProviderInvoice[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice", cascade={"persist"}, mappedBy="provider", orphanRemoval=true)
     */
    private $invoices;

    /**
     * @var User[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\User\Entity\User\User", mappedBy="exclude_providers")
     */
    private $exclude_provider_users;

    /**
     * @var zapCardStock[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\Card\Entity\Stock\ZapCardStock", mappedBy="providers")
     */
    private $stocks;

    /**
     * @var IncomeDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Document\IncomeDocument", mappedBy="provider")
     */
    private $incomeDocuments;

    /**
     * @var IncomeOrder[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Order\IncomeOrder", cascade={"persist"}, mappedBy="provider")
     */
    private $income_orders;

    /**
     * @var FirmBalanceHistory[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory", mappedBy="provider")
     */
    private $firm_balance_history;

    public function __construct(string $name, User $user, ZapSklad $zapSklad, ?string $koef_dealer, bool $isDealer)
    {
        $this->name = $name;
        $this->user = $user;
        $this->zapSklad = $zapSklad;
        $this->koef_dealer = str_replace(',', '.', $koef_dealer);
        $this->isDealer = $isDealer;
        $this->income_orders = new ArrayCollection();
        $this->prices = new ArrayCollection();
        $this->invoices = new ArrayCollection();
    }

    public function update(string $name, User $user, ZapSklad $zapSklad, ?string $koef_dealer, bool $isDealer)
    {
        $this->name = $name;
        $this->user = $user;
        $this->zapSklad = $zapSklad;
        $this->koef_dealer = str_replace(',', '.', $koef_dealer);
        $this->isDealer = $isDealer;
    }

    public function updateEmail(?int $incomeOrderNumber, ?string $incomeOrderSubject, ?string $incomeOrderText, ?string $incomeOrderSubject5, ?string $incomeOrderText5, ?string $incomeOrderEmail, bool $isIncomeOrder)
    {
        $this->incomeOrderNumber = $incomeOrderNumber ?: 1;
        $this->incomeOrderSubject = $incomeOrderSubject ?: '';
        $this->incomeOrderText = $incomeOrderText ?: '';
        $this->incomeOrderSubject5 = $incomeOrderSubject5 ?: '';
        $this->incomeOrderText5 = $incomeOrderText5 ?: '';
        $this->incomeOrderEmail = $incomeOrderEmail ?: '';
        $this->isIncomeOrder = $isIncomeOrder;
    }

    public function updateSend(bool $isIncomeOrderAutoSend, ?array $incomeOrderWeekDays, ?string $incomeOrderTime)
    {
        $this->isIncomeOrderAutoSend = $isIncomeOrderAutoSend;
        $this->incomeOrderWeekDays = $incomeOrderWeekDays ? json_encode($incomeOrderWeekDays) : '';
        $this->incomeOrderTime = $incomeOrderTime ?: '';
    }

    public function updateCurrencyForAllProviderPrices(Currency $currency, string $koef, string $currencyOwn)
    {
        foreach ($this->prices as $price) {
            $price->updateFromProvider(
                $currency,
                $koef,
                $currencyOwn
            );
        }
    }

    public function assignPrice(ProviderPrice $price): void
    {
        $this->prices->add($price);
    }

    public function clearPriceProfits()
    {
        foreach ($this->prices as $price) {
            $price->clearProfits();
        }
    }

    public function assignInvoice(ProviderInvoice $invoice): void
    {
        $this->invoices->add($invoice);
    }

    public function getId(): ?int
    {
        return $this->providerID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return ZapSklad
     */
    public function getZapSklad(): ZapSklad
    {
        return $this->zapSklad;
    }

    /**
     * @return float
     */
    public function getKoefDealer(): float
    {
        return $this->koef_dealer;
    }

    /**
     * @return bool
     */
    public function isMain(): bool
    {
        return $this->isMain;
    }

    /**
     * @return bool
     */
    public function isHide(): bool
    {
        return $this->isHide;
    }

    /**
     * @return bool
     */
    public function isDealer(): bool
    {
        return $this->isDealer;
    }

    /**
     * @return IncomeOrder[]|ArrayCollection
     */
    public function getIncomeOrders()
    {
        return $this->income_orders;
    }

    public function assignIncomeOrderAndReturn(ZapSklad $zapSklad, int $document_num): IncomeOrder
    {
        $incomeOrder = new IncomeOrder($this, $zapSklad, $document_num);
        $this->income_orders->add($incomeOrder);
        return $incomeOrder;
    }

    /**
     * @return int
     */
    public function getIncomeOrderNumber(): int
    {
        return $this->incomeOrderNumber;
    }

    /**
     * @return string
     */
    public function getIncomeOrderSubject(): string
    {
        return $this->incomeOrderSubject;
    }

    /**
     * @return string
     */
    public function getIncomeOrderText(): string
    {
        return $this->incomeOrderText;
    }

    /**
     * @return string
     */
    public function getIncomeOrderSubject5(): string
    {
        return $this->incomeOrderSubject5;
    }

    /**
     * @return string
     */
    public function getIncomeOrderText5(): string
    {
        return $this->incomeOrderText5;
    }

    /**
     * @return string
     */
    public function getIncomeOrderEmail(): string
    {
        return $this->incomeOrderEmail;
    }

    /**
     * @return bool
     */
    public function isIncomeOrder(): bool
    {
        return $this->isIncomeOrder;
    }

    /**
     * @return bool
     */
    public function isIncomeOrderAutoSend(): bool
    {
        return $this->isIncomeOrderAutoSend;
    }

    /**
     * @return array
     */
    public function getIncomeOrderWeekDays(): array
    {
        if (empty($this->incomeOrderWeekDays)) return [];
        return json_decode($this->incomeOrderWeekDays, true);
    }
    
    public function getIncomeOrderWeekDaysValues(): array
    {
        $weeks = [];
        if ($this->getIncomeOrderWeekDays()) {
            foreach ($this->getIncomeOrderWeekDays() as $weekDay) {
                if (isset(self::WEEKS[$weekDay])) $weeks[] = self::WEEKS[$weekDay];
            }
        }
        return $weeks;
    }

    /**
     * @return string
     */
    public function getIncomeOrderTime(): string
    {
        return $this->incomeOrderTime;
    }

    /**
     * @return ProviderPrice[]|array
     */
    public function getPrices(): array
    {
        return $this->prices->toArray();
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
