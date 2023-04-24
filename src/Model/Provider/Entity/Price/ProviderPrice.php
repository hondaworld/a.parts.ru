<?php

namespace App\Model\Provider\Entity\Price;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Detail\Entity\PriceExclude\DetailProviderPriceExclude;
use App\Model\Finance\Entity\Currency\Currency;
use App\Model\Income\Entity\Income\Income;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Provider\Entity\Group\ProviderPriceGroup;
use App\Model\Provider\Entity\LogPrice\LogPrice;
use App\Model\Provider\Entity\LogPriceAll\LogPriceAll;
use App\Model\Provider\Entity\Opt\ProviderPriceOpt;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Shop\Entity\ShopPrice\ShopPrice1;
use App\Model\Shop\Entity\ShopPrice\ShopPrice10;
use App\Model\Shop\Entity\ShopPrice\ShopPrice2;
use App\Model\Shop\Entity\ShopPrice\ShopPrice3;
use App\Model\Shop\Entity\ShopPrice\ShopPrice4;
use App\Model\Shop\Entity\ShopPrice\ShopPrice5;
use App\Model\Shop\Entity\ShopPrice\ShopPrice6;
use App\Model\Shop\Entity\ShopPrice\ShopPrice7;
use App\Model\Shop\Entity\ShopPrice\ShopPrice8;
use App\Model\Shop\Entity\ShopPrice\ShopPrice9;
use App\Model\Shop\Entity\ShopPrice\ShopPriceN;
use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @ORM\Entity(repositoryClass=ProviderPriceRepository::class)
 * @ORM\Table(name="providerPrices")
 */
class ProviderPrice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="providerPriceID")
     */
    private $providerPriceID;

    /**
     * @var Provider
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\Provider\Provider", inversedBy="prices")
     * @ORM\JoinColumn(name="providerID", referencedColumnName="providerID")
     */
    private $provider;

    /**
     * @var ProviderPriceGroup
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\Group\ProviderPriceGroup")
     * @ORM\JoinColumn(name="providerPriceGroupID", referencedColumnName="providerPriceGroupID")
     */
    private $group;

    /**
     * @var Currency
     * @ORM\ManyToOne(targetEntity="App\Model\Finance\Entity\Currency\Currency")
     * @ORM\JoinColumn(name="currencyID", referencedColumnName="currencyID")
     */
    private $currency;

    /**
     * @var ProviderPriceOpt[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Provider\Entity\Opt\ProviderPriceOpt", cascade={"persist"}, mappedBy="providerPrice", orphanRemoval=true)
     */
    private $profits;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $srok;

    /**
     * @ORM\Column(type="integer", name="srokInDays")
     */
    private $srokInDays;

    /**
     * @ORM\Column(type="koef", precision=4, scale=2)
     */
    private $koef;

    /**
     * @ORM\Column(type="koef", precision=5, scale=2, name="currency")
     */
    private $currencyOwn;

    /**
     * @ORM\Column(type="koef", precision=6, scale=2, name="forWeight")
     */
    private $deliveryForWeight;

    /**
     * @ORM\Column(type="integer", name="delivery")
     */
    private $deliveryInPercent;

    /**
     * @ORM\Column(type="integer", name="daysofchanged")
     */
    private $daysofchanged;

    /**
     * @ORM\Column(type="koef", precision=4, scale=2, name="discount")
     */
    private $discount;

    /**
     * @ORM\Column(type="boolean", name="clients_hide")
     */
    private $clients_hide;

    /**
     * @var Price
     * @ORM\Embedded(class="Price", columnPrefix=false)
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     */
    private $countofdetails = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofchanged;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="string")
     */
    private $rows_done = '';

    /**
     * @ORM\Column(type="string")
     */
    private $rows_error = '';

    /**
     * @var Num
     * @ORM\Embedded(class="Num")
     */
    private $num;

    /**
     * @var ProviderPrice
     * @ORM\ManyToOne(targetEntity="ProviderPrice", inversedBy="childrenProviderPrices")
     * @ORM\JoinColumn(name="superProviderPriceID", referencedColumnName="providerPriceID", nullable=true)
     */
    private $superProviderPrice;

    /**
     * @var ProviderPrice[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="ProviderPrice", mappedBy="superProviderPrice")
     */
    private $childrenProviderPrices;

    /**
     * @var Creater
     * @ORM\ManyToOne(targetEntity="App\Model\Detail\Entity\Creater\Creater", fetch="EAGER")
     * @ORM\JoinColumn(name="createrID", referencedColumnName="createrID", nullable=true)
     */
    private $creater;

    /**
     * @var ShopPrice1[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice1", mappedBy="providerPrice", orphanRemoval=true)
     */
    private $shopPrice1;

    /**
     * @var ShopPrice2[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice2", mappedBy="providerPrice", orphanRemoval=true)
     */
    private $shopPrice2;

    /**
     * @var ShopPrice3[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice3", mappedBy="providerPrice", orphanRemoval=true)
     */
    private $shopPrice3;

    /**
     * @var ShopPrice4[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice4", mappedBy="providerPrice", orphanRemoval=true)
     */
    private $shopPrice4;

    /**
     * @var ShopPrice5[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice5", mappedBy="providerPrice", orphanRemoval=true)
     */
    private $shopPrice5;

    /**
     * @var ShopPrice6[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice6", mappedBy="providerPrice", orphanRemoval=true)
     */
    private $shopPrice6;

    /**
     * @var ShopPrice7[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice7", mappedBy="providerPrice", orphanRemoval=true)
     */
    private $shopPrice7;

    /**
     * @var ShopPrice8[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice8", mappedBy="providerPrice", orphanRemoval=true)
     */
    private $shopPrice8;

    /**
     * @var ShopPrice9[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice9", mappedBy="providerPrice", orphanRemoval=true)
     */
    private $shopPrice9;

    /**
     * @var ShopPrice10[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice10", mappedBy="providerPrice", orphanRemoval=true)
     */
    private $shopPrice10;

    /**
     * @var ShopPriceN[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPriceN", mappedBy="providerPrice", orphanRemoval=true)
     */
    private $shopPriceN;

    /**
     * @var LogPrice[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Provider\Entity\LogPrice\LogPrice", mappedBy="providerPrice", orphanRemoval=true)
     */
    private $logs;

    /**
     * @var LogPriceAll[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Provider\Entity\LogPriceAll\LogPriceAll", mappedBy="providerPrice", orphanRemoval=true)
     */
    private $logs_all;

    /**
     * @var User[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\User\Entity\User\User", mappedBy="show_hide_prices")
     */
    private $show_hide_price_users;

    /**
     * @var ZapCard[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Card\ZapCard", mappedBy="currency_providerPrice")
     */
    private $zapCards;

    /**
     * @var DetailProviderPriceExclude[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Detail\Entity\PriceExclude\DetailProviderPriceExclude", mappedBy="providerPrice", orphanRemoval=true)
     */
    private $priceExclude;

    /**
     * @var Income[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Income\Income", mappedBy="provider_price")
     */
    private $incomes;

    /**
     * @var OrderGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Good\OrderGood", mappedBy="providerPrice")
     */
    private $order_goods;

    public function __construct(
        ProviderPriceGroup $providerPriceGroup,
        Provider $provider,
        string $name,
        string $description,
        string $srok,
        int $srokInDays,
        Currency $currency,
        string $koef,
        ?string $currencyOwn,
        ?string $deliveryForWeight,
        ?int $deliveryInPercent,
        string $discount,
        int $daysofchanged,
        bool $clients_hide
    )
    {
        $this->group = $providerPriceGroup;
        $this->provider = $provider;
        $this->name = $name;
        $this->description = $description;
        $this->srok = $srok;
        $this->srokInDays = $srokInDays;
        $this->currency = $currency;
        $this->koef = $koef;
        $this->currencyOwn = $currencyOwn;
        $this->deliveryForWeight = $deliveryForWeight;
        $this->deliveryInPercent = $deliveryInPercent ?: 0;
        $this->discount = $discount;
        $this->daysofchanged = $daysofchanged;
        $this->clients_hide = $clients_hide;
        $this->num = new Num();
        $this->price = new Price();
        $this->profits = new ArrayCollection();
    }

    public function update(
        ProviderPriceGroup $providerPriceGroup,
        Provider $provider,
        string $name,
        string $description,
        string $srok,
        int $srokInDays,
        Currency $currency,
        string $koef,
        ?string $currencyOwn,
        ?string $deliveryForWeight,
        ?int $deliveryInPercent,
        string $discount,
        int $daysofchanged,
        bool $clients_hide
    )
    {
        $this->group = $providerPriceGroup;
        $this->provider = $provider;
        $this->name = $name;
        $this->description = $description;
        $this->srok = $srok;
        $this->srokInDays = $srokInDays;
        $this->currency = $currency;
        $this->koef = $koef;
        $this->currencyOwn = $currencyOwn;
        $this->deliveryForWeight = $deliveryForWeight;
        $this->deliveryInPercent = $deliveryInPercent ?: 0;
        $this->discount = $discount;
        $this->daysofchanged = $daysofchanged;
        $this->clients_hide = $clients_hide;
    }

    public function updatePrice(
        Price $price,
        ?ProviderPrice $superProviderPrice,
        ?Creater $creater
    )
    {
        $this->price = $price;
        $this->superProviderPrice = $superProviderPrice;
        $this->creater = $creater;
    }

    public function updateFromProvider(
        Currency $currency,
        string $koef,
        ?string $currencyOwn
    )
    {
        $this->currency = $currency;
        $this->koef = $koef;
        $this->currencyOwn = $currencyOwn;
    }

    public function updateFileData(
        int $countofdetails,
        array $rows_done,
        array $rows_error
    )
    {
        $this->countofdetails = $countofdetails;
        $this->rows_done = (count($rows_done) > 0 ? json_encode($rows_done) : '');
        $this->rows_error = (count($rows_error) > 0 ? json_encode($rows_error) : '');
        $this->dateofchanged = new \DateTime();
    }

    public function updateNum(Num $num)
    {
        $this->num = $num;
    }

    public function assignProfit(Opt $opt, string $profit)
    {
        $this->profits->add(new ProviderPriceOpt($this, $opt, $profit));
    }

    public function clearProfits()
    {
        $this->profits->clear();
    }

    public function getId(): int
    {
        return $this->providerPriceID;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return Provider
     */
    public function getProvider(): Provider
    {
        return $this->provider;
    }

    /**
     * @return ProviderPriceGroup
     */
    public function getGroup(): ProviderPriceGroup
    {
        return $this->group;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * @return ProviderPriceOpt[]|ArrayCollection
     */
    public function getProfits(): array
    {
        return $this->profits->toArray();
    }

    public function getSrok(): string
    {
        return $this->srok;
    }

    public function getSrokInDays(): int
    {
        return $this->srokInDays;
    }

    public function getKoef(): string
    {
        return $this->koef;
    }

    public function getCurrencyOwn(): string
    {
        return $this->currencyOwn;
    }

    public function getDeliveryForWeight(): string
    {
        return $this->deliveryForWeight;
    }

    public function getDeliveryInPercent(): string
    {
        return $this->deliveryInPercent;
    }

    public function getDaysofchanged(): int
    {
        return $this->daysofchanged;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function isClientsHide(): bool
    {
        return $this->clients_hide;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    public function getCountofdetails(): int
    {
        return $this->countofdetails;
    }

    public function getDateofchanged(): ?\DateTimeImmutable
    {
        if ($this->dateofchanged && $this->dateofchanged->format('Y') == '-0001') return null;
        return $this->dateofchanged;
    }

    public function getNum(): Num
    {
        return $this->num;
    }

    public function isHide()
    {
        return $this->isHide;
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unHide(): void
    {
        $this->isHide = false;
    }

    /**
     * @return ProviderPrice
     */
    public function getSuperProviderPrice(): ?ProviderPrice
    {
        return $this->superProviderPrice;
    }

    public function removeSuperProviderPrice(): void
    {
        $this->superProviderPrice = null;
    }

    /**
     * @return Creater
     */
    public function getCreater(): ?Creater
    {
        return $this->creater;
    }

    /**
     * @return void
     */
    public function clearChildrenProviderPrices(): void
    {
        foreach ($this->childrenProviderPrices as $childrenProviderPrice) {
            $childrenProviderPrice->removeSuperProviderPrice();
        }
        $this->childrenProviderPrices->clear();
    }

    /**
     * @return ProviderPrice[]|array
     */
    public function getChildrenProviderPrices(): array
    {
        return $this->childrenProviderPrices->toArray();
    }

    public function getFullName(): string
    {
        return $this->getName() . ' ' . $this->getDescription();
    }

    public function getDiscountParts($discountParts): float
    {
        if ($discountParts > $this->discount)
            return $this->discount;
        else
            return $discountParts;
    }

    /**
     * @return array
     */
    public function getRowsDone(): array
    {
        return empty($this->rows_done) ? [] : json_decode($this->rows_done, true);
    }

    /**
     * @return array
     */
    public function getRowsError(): array
    {
        $result = empty($this->rows_error) ? [] : json_decode($this->rows_error, true);
        $result = $result ?: [];
        ksort($result);
        return $result;
    }

    /**
     * @return OrderGood[]|ArrayCollection
     */
    public function getOrderGoods()
    {
        return $this->order_goods;
    }

    /**
     * @return Income[]|ArrayCollection
     */
    public function getIncomes()
    {
        return $this->incomes;
    }


}
