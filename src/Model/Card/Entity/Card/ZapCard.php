<?php

namespace App\Model\Card\Entity\Card;

use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Auto\Entity\MotoModel\MotoModel;
use App\Model\Card\Entity\Abc\Abc;
use App\Model\Card\Entity\Abc\ZapCardAbc;
use App\Model\Card\Entity\Abc\ZapCardAbcHistory;
use App\Model\Card\Entity\Auto\ZapCardAuto;
use App\Model\Card\Entity\FakePhoto\ZapCardFakePhoto;
use App\Model\Card\Entity\Location\ZapSkladLocation;
use App\Model\Card\Entity\Measure\EdIzm;
use App\Model\Card\Entity\Group\ZapGroup;
use App\Model\Card\Entity\Opt\ZapCardOpt;
use App\Model\Card\Entity\Photo\ZapCardPhoto;
use App\Model\Card\Entity\ReserveSklad\ZapCardReserveSklad;
use App\Model\Contact\Entity\Country\Country;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use App\Model\Finance\Entity\Currency\Currency;
use App\Model\Income\Entity\Good\IncomeGood;
use App\Model\Income\Entity\Income\Income;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Reseller\Entity\Avito\AvitoNotice;
use App\Model\Shop\Entity\Location\ShopLocation;
use App\Model\Shop\Entity\ShopType\ShopType;
use App\Model\Sklad\Entity\PriceGroup\PriceGroup;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\User\Entity\Opt\Opt;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZapCardRepository::class)
 * @ORM\Table(name="zapCards")
 */
class ZapCard
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="zapCardID")
     */
    private $zapCardID;

    /**
     * @var ZapGroup
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Group\ZapGroup", inversedBy="zapCards", fetch="EAGER")
     * @ORM\JoinColumn(name="zapGroupID", referencedColumnName="zapGroupID", nullable=true)
     */
    private $zapGroup;

    /**
     * @var ShopType
     * @ORM\ManyToOne(targetEntity="App\Model\Shop\Entity\ShopType\ShopType", inversedBy="zapCards")
     * @ORM\JoinColumn(name="shop_typeID", referencedColumnName="shop_typeID", nullable=false)
     */
    private $shop_type;

    /**
     * @var Creater
     * @ORM\ManyToOne(targetEntity="App\Model\Detail\Entity\Creater\Creater", inversedBy="zapCards", fetch="EAGER")
     * @ORM\JoinColumn(name="createrID", referencedColumnName="createrID", nullable=false)
     */
    private $creater;

    /**
     * @var PriceGroup
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\PriceGroup\PriceGroup", inversedBy="zapCards")
     * @ORM\JoinColumn(name="price_groupID", referencedColumnName="price_groupID", nullable=true)
     */
    private $price_group;

    /**
     * @ORM\Column(type="boolean")
     */
    private $is_price_group_fix = false;

    /**
     * @ORM\Column(type="string", length=255, name="nameEng")
     */
    private $nameEng = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name_big = '';

    /**
     * @var DetailNumber
     * @ORM\Column(type="detail_number", length=30)
     */
    private $number;

    /**
     * @ORM\Column(type="koef", precision=5, scale=2)
     */
    private $price = 0;

    /**
     * @ORM\Column(type="koef", precision=5, scale=2)
     */
    private $currency_price = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $profit = 0;

    /**
     * @ORM\Column(type="koef", precision=5, scale=2)
     */
    private $price1 = 0;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $price_service = '';

    /**
     * @var \DateTime
     * @ORM\Column(type="date", name="dateofdeleted", nullable=true)
     */
    private $dateofdeleted;

    /**
     * @ORM\Column(type="boolean", name="isDeleted")
     */
    private $isDeleted = false;

    /**
     * @ORM\Column(type="text")
     */
    private $text = '';

    /**
     * @ORM\Column(type="text")
     */
    private $text_fake = '';

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="zapCards")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=true)
     */
    private $manager;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", name="date_of_opt_profit_changed", nullable=true)
     */
    private $date_of_opt_profit_changed;

    /**
     * @var Country
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Country\Country", inversedBy="zapCards")
     * @ORM\JoinColumn(name="countryID", referencedColumnName="countryID", nullable=true)
     */
    private $country;

    /**
     * @var ProviderPrice
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\Price\ProviderPrice", inversedBy="zapCards")
     * @ORM\JoinColumn(name="currency_providerPriceID", referencedColumnName="providerPriceID", nullable=true)
     */
    private $currency_providerPrice;

    /**
     * @var Currency
     * @ORM\ManyToOne(targetEntity="App\Model\Finance\Entity\Currency\Currency", inversedBy="zapCards")
     * @ORM\JoinColumn(name="currencyID", referencedColumnName="currencyID", nullable=true)
     */
    private $currency;

    /**
     * @var EdIzm
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Measure\EdIzm", inversedBy="zapCards")
     * @ORM\JoinColumn(name="ed_izmID", referencedColumnName="ed_izmID", nullable=false)
     */
    private $ed_izm;

    /**
     * @var ZapCardOpt[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Opt\ZapCardOpt", cascade={"persist"}, mappedBy="zapCard", orphanRemoval=true)
     */
    private $profits;

    /**
     * @var ZapCardAbc[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Abc\ZapCardAbc", cascade={"persist"}, mappedBy="zapCard", orphanRemoval=true)
     */
    private $abc;

    /**
     * @var ZapCardAbcHistory[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Abc\ZapCardAbcHistory", cascade={"persist"}, mappedBy="zapCard", orphanRemoval=true)
     * @ORM\OrderBy({"dateofadded" = "DESC", "id" = "DESC"})
     */
    private $abc_history;

    /**
     * @var ZapCardPhoto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Photo\ZapCardPhoto", cascade={"persist"}, mappedBy="zapCard", orphanRemoval=true)
     */
    private $photos;

    /**
     * @var ZapCardFakePhoto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\FakePhoto\ZapCardFakePhoto", cascade={"persist"}, mappedBy="zapCard", orphanRemoval=true)
     */
    private $fake_photos;

    /**
     * @var ZapCardAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Auto\ZapCardAuto", cascade={"persist"}, mappedBy="zapCard")
     */
    private $autos;

    /**
     * @var ZapSkladLocation[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Location\ZapSkladLocation", cascade={"persist"}, mappedBy="zapCard", orphanRemoval=true)
     */
    private $locations;

    /**
     * @var Income[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Income\Income", mappedBy="zapCard")
     */
    private $incomes;

    /**
     * @var IncomeGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Good\IncomeGood", mappedBy="zapCard")
     */
    private $income_goods;

    /**
     * @var ExpenseSklad[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Sklad\ExpenseSklad", mappedBy="zapCard")
     */
    private $expense_sklads;

    /**
     * @var ZapCardReserveSklad[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\ReserveSklad\ZapCardReserveSklad", mappedBy="zapCard")
     */
    private $zapCardReserveSklad;

    /**
     * @var AvitoNotice[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Reseller\Entity\Avito\AvitoNotice", mappedBy="zapCard")
     */
    private $avito_notices;

    public function __construct(
        DetailNumber $number,
        Creater      $creater,
        ShopType     $shop_type,
        ?ZapGroup    $zapGroup,
        ?string      $name,
        ?string      $description,
        PriceGroup   $price_group,
        EdIzm        $ed_izm
    )
    {
        $this->number = $number;
        $this->creater = $creater;
        $this->shop_type = $shop_type;
        $this->zapGroup = $zapGroup;
        $this->name = $name ?: '';
        $this->description = $description ?: '';
        $this->price_group = $price_group;
        $this->ed_izm = $ed_izm;
        $this->locations = new ArrayCollection();
        $this->profits = new ArrayCollection();
        $this->autos = new ArrayCollection();
        $this->abc = new ArrayCollection();
        $this->abc_history = new ArrayCollection();
    }

    public function updateNumber(
        DetailNumber $number,
        Creater      $creater
    )
    {
        $this->number = $number;
        $this->creater = $creater;
    }

    public function updateName(
        ?ZapGroup $zapGroup,
        ?string   $name,
        ?string   $description,
        ?string   $name_big,
        ?string   $nameEng
    )
    {
        $this->zapGroup = $zapGroup;
        $this->name = $name ?: '';
        $this->description = $description ?: '';
        $this->name_big = $name_big ?: '';
        $this->nameEng = $nameEng ?: '';
    }

    public function updateDop(
        ?Country $country,
        ShopType $shop_type,
        EdIzm    $ed_izm
    )
    {
        $this->country = $country;
        $this->shop_type = $shop_type;
        $this->ed_izm = $ed_izm;
    }

    public function updateDescription(
        ?string $text,
        ?string $text_fake
    )
    {
        $this->text = ($text === null ? '' : $text);
        $this->text_fake = ($text_fake === null ? '' : $text_fake);
    }

    public function updatePriceGroup(
        ?PriceGroup $price_group,
        bool        $is_price_group_fix
    )
    {
        $this->price_group = $price_group;
        $this->is_price_group_fix = $is_price_group_fix;
    }

    public function updatePriceGroupFix(
        bool $is_price_group_fix
    )
    {
        $this->is_price_group_fix = $is_price_group_fix;
    }

    public function updatePrice(
        ?string        $price,
        ?string        $currency_price,
        ?ProviderPrice $currency_providerPrice,
        ?Currency      $currency
    )
    {
        $this->price = $price ?: 0;
        $this->currency_price = $currency_price ?: 0;
        $this->currency_providerPrice = $currency_providerPrice;
        $this->currency = $currency;
    }

    public function updatePriceOnly(string $price)
    {
        $this->price = $price;
    }

    public function updatePriceService(?string $price_service)
    {
        $this->price_service = $price_service ?: 0;
    }

    public function updateProfit(
        ?string $price1,
        ?int    $profit
    )
    {
        $this->price1 = $price1 ?: 0;
        $this->profit = $profit ?: 0;
    }

    public function updateCountry(?Country $country)
    {
        $this->country = $country;
    }

    public function updateManager(?Manager $manager)
    {
        $this->manager = $manager;
    }

    public function updateDateOfOptProfitChanged(\DateTime $date = null)
    {
        $this->date_of_opt_profit_changed = $date ?: new \DateTime();
    }

    public function assignZapCardOpt(Opt $opt, string $profit)
    {
        $this->profits->add(new ZapCardOpt($this, $opt, $profit));
    }

    public function clearZapCardOpt(): void
    {
        $this->profits->clear();
    }

    public function assignZapCardAuto(?AutoModel $auto_model, ?MotoModel $moto_model, int $year)
    {
        $isExist = false;
        foreach ($this->autos as $auto) {
            if ($auto->getAutoModel() === $auto_model && $auto->getMotoModel() === $moto_model && $auto->getYear() == $year) {
                $isExist = true;
            }
        }
        if (!$isExist) {
            $this->autos->add(new ZapCardAuto($this, $auto_model, $moto_model, $year));
        }
    }

    /**
     * @return ZapCardAuto[]|ArrayCollection
     */
    public function getAutos()
    {
        return $this->autos;
    }

    public function getId(): ?int
    {
        return $this->zapCardID;
    }

    public function getZapGroup(): ?ZapGroup
    {
        return $this->zapGroup;
    }

    public function getShopType(): ShopType
    {
        return $this->shop_type;
    }

    public function getCreater(): Creater
    {
        return $this->creater;
    }

    public function getPriceGroup(): ?PriceGroup
    {
        return $this->price_group;
    }

    public function isPriceGroupFix(): ?bool
    {
        return $this->is_price_group_fix;
    }

    public function getNameEng(): ?string
    {
        return $this->nameEng;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getNameBig(): ?string
    {
        return $this->name_big;
    }

    public function getNumber(): DetailNumber
    {
        return $this->number;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function removePriceGroup(): void
    {
        $this->price_group = null;
    }

    /**
     * @return EdIzm
     */
    public function getEdIzm(): EdIzm
    {
        return $this->ed_izm;
    }

    /**
     * @return ZapCardOpt[]|array
     */
    public function getProfits(): array
    {
        return $this->profits->toArray();
    }

    /**
     * @return string
     */
    public function getPrice(): string
    {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getPriceService(): string
    {
        return $this->price_service;
    }

    /**
     * @return int
     */
    public function getProfit(): int
    {
        return $this->profit;
    }

    /**
     * @return string
     */
    public function getPrice1(): string
    {
        return $this->price1;
    }

    public function getDateofdeleted(): ?\DateTime
    {
        if ($this->dateofdeleted && $this->dateofdeleted->format('Y') == '-0001') return null;
        return $this->dateofdeleted;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getTextFake(): string
    {
        return $this->text_fake;
    }

    public function getDateOfOptProfitChanged(): ?\DateTime
    {
        return $this->date_of_opt_profit_changed;
    }

    public function isShowProfitForm(int $days): bool
    {
        if (empty($this->getDateOfOptProfitChanged())) return true;
        return $this->getDateOfOptProfitChanged()->modify('+ ' . $days . ' days') < new \DateTime();
    }


    public function delete(): void
    {
        $this->isDeleted = true;
        $this->dateofdeleted = new \DateTime();
    }

    public function restore(): void
    {
        $this->isDeleted = false;
        $this->dateofdeleted = null;
    }

    public function getDetailName(): string
    {
        $detailName = "";
        if ($this->zapGroup == null) {
            $detailName = $this->name_big;
        } else {
            $detailName = $this->zapGroup->getName();
            if ($this->name != "") $detailName .= " " . $this->name;
            if ($this->description != "") $detailName .= " " . $this->description;
        }
        return $detailName;
    }

    public function updateAbc(ZapSklad $zapSklad, string $abcNew, Manager $manager)
    {
        $abcNew = trim($abcNew);

        $abc = $this->getAbcByZapSklad($zapSklad);

        if (!$abc || $abc->getAbc() != $abcNew) {
            if ($abcNew == '') {
                if ($this->abc->contains($abc)) {
                    $this->abc->removeElement($abc);
                }
            } else if ($abc) {
                $abc->update($abcNew);
            } else {
                $this->abc->add(new ZapCardAbc($this, $zapSklad, $abcNew));
            }

            $this->abc_history->add(new ZapCardAbcHistory($this, $zapSklad, $abcNew, $manager));
        }
    }

    /**
     * @param ZapSklad $zapSklad
     * @return ZapCardAbc|null
     */
    public function getAbcByZapSklad(ZapSklad $zapSklad): ?ZapCardAbc
    {
        foreach ($this->abc as $item) {
            if ($item->getZapSklad()->getId() == $zapSklad->getId()) return $item;
        }
        return null;
    }

    /**
     * @param int $zapSkladID
     * @return string|null
     */
    public function getZapCardAbc(int $zapSkladID): ?string
    {
        foreach ($this->abc as $item) {
            if ($item->getZapSklad()->getId() == $zapSkladID) return $item->getAbc();
        }
        return null;
    }

    /**
     * @param int $zapSkladID
     * @return array
     */
    public function getAbcHistory(int $zapSkladID): array
    {
        $array = [];
        foreach ($this->abc_history as $item) {
            if ($item->getZapSklad()->getId() == $zapSkladID) {
                $array[] = [
                    'abc' => $item->getAbc(),
                    'dateofadded' => $item->getDateofadded()->format('d.m.Y'),
                    'manager' => $item->getManager()->getName()
                ];
            }
        }
        return $array;
    }

    /**
     * @return string
     */
    public function getCurrencyPrice(): string
    {
        return $this->currency_price;
    }

    /**
     * @return ProviderPrice
     */
    public function getCurrencyProviderPrice(): ?ProviderPrice
    {
        return $this->currency_providerPrice;
    }

    /**
     * @return Currency
     */
    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    /**
     * @return ZapCardPhoto[]|ArrayCollection
     */
    public function getSortedPhotos(): array
    {
        $photos = $this->photos->toArray();
        usort($photos, function (ZapCardPhoto $a, ZapCardPhoto $b) {
            if ($a->isMain() == $b->isMain()) return $a->getId() - $b->getId();
            return $b->isMain() - $a->isMain();
        });
        return $photos;
    }

    /**
     * @return ZapCardPhoto[]|ArrayCollection
     */
    public function getPhotos(): array
    {
        return $this->photos->toArray();
    }

    /**
     * @return ZapCardPhoto|null
     */
    public function getMainPhoto(): ?ZapCardPhoto
    {
        if ($this->photos->count() == 0) return null;

        $photos = $this->photos->toArray();
        foreach ($photos as $photo) {
            if ($photo->isMain()) return $photo;
        }
        return $photos[0];
    }

    /**
     * @return ZapCardFakePhoto[]|ArrayCollection
     */
    public function getFakePhotos(): array
    {
        return $this->fake_photos->toArray();
    }

    /**
     * @return ZapSkladLocation[]|ArrayCollection
     */
    public function getLocations()
    {
        return $this->locations;
    }

    public function assignLocation(ZapSklad $zapSklad, ?ShopLocation $location = null): void
    {
        if (!$this->getLocationByZapSklad($zapSklad)) {
            $this->locations->add(new ZapSkladLocation($this, $zapSklad, $location));
        }
    }

    /**
     * @param ZapSklad $zapSklad
     * @return ZapSkladLocation|null
     */
    public function getLocationByZapSklad(ZapSklad $zapSklad): ?ZapSkladLocation
    {
        foreach ($this->locations as $location) {
            if ($location->getZapSklad()->getId() == $zapSklad->getId()) return $location;
        }
        return null;
    }

}
