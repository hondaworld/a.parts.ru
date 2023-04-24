<?php

namespace App\Model\Detail\Entity\Creater;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\StockNumber\ZapCardStockNumber;
use App\Model\Detail\Entity\Dealer\ShopPriceDealer;
use App\Model\Detail\Entity\PriceExclude\DetailProviderPriceExclude;
use App\Model\Detail\Entity\ProviderExclude\DetailProviderExclude;
use App\Model\Detail\Entity\Weight\Weight;
use App\Model\Detail\Entity\Zamena\ShopZamena;
use App\Model\Firm\Entity\SchetGood\SchetGood;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Order\Entity\Site\Site;
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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @ORM\Entity(repositoryClass=CreaterRepository::class)
 * @ORM\Table(name="creaters")
 */
class Creater
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="createrID")
     */
    private $createrID;

    /**
     * @var Creater
     * @ORM\ManyToOne(targetEntity="Creater")
     * @ORM\JoinColumn(name="creater_weightID", referencedColumnName="createrID", nullable=true)
     */
    private $creater_weight;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name_rus;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name_avito = '';

    /**
     * @ORM\Column(type="boolean", name="isOriginal")
     */
    private $isOriginal = false;

    /**
     * @ORM\Column(type="boolean", name="isUSA")
     */
    private $isUSA = false;

    /**
     * @ORM\Column(type="koef", precision=9, scale=3, name="koefUsa")
     */
    private $koefUsa = 1;

    /**
     * @ORM\Column(type="string", length=30, name="tableName")
     */
    private $tableName;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $tire = '';

    /**
     * @ORM\Column(type="boolean", name="isFast")
     */
    private $isFast = false;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $photo = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $logo_big = '';

    /**
     * @ORM\Column(type="text")
     */
    private $catalogs = '';

    /**
     * @ORM\Column(type="text")
     */
    private $alt_names = '';

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var ShopPrice1[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice1", mappedBy="creater", orphanRemoval=true)
     */
    private $shopPrice1;

    /**
     * @var ShopPrice2[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice2", mappedBy="creater", orphanRemoval=true)
     */
    private $shopPrice2;

    /**
     * @var ShopPrice3[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice3", mappedBy="creater", orphanRemoval=true)
     */
    private $shopPrice3;

    /**
     * @var ShopPrice4[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice4", mappedBy="creater", orphanRemoval=true)
     */
    private $shopPrice4;

    /**
     * @var ShopPrice5[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice5", mappedBy="creater", orphanRemoval=true)
     */
    private $shopPrice5;

    /**
     * @var ShopPrice6[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice6", mappedBy="creater", orphanRemoval=true)
     */
    private $shopPrice6;

    /**
     * @var ShopPrice7[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice7", mappedBy="creater", orphanRemoval=true)
     */
    private $shopPrice7;

    /**
     * @var ShopPrice8[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice8", mappedBy="creater", orphanRemoval=true)
     */
    private $shopPrice8;

    /**
     * @var ShopPrice9[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice9", mappedBy="creater", orphanRemoval=true)
     */
    private $shopPrice9;

    /**
     * @var ShopPrice10[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPrice10", mappedBy="creater", orphanRemoval=true)
     */
    private $shopPrice10;

    /**
     * @var ShopPriceN[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Shop\Entity\ShopPrice\ShopPriceN", mappedBy="creater", orphanRemoval=true)
     */
    private $shopPriceN;

    /**
     * @var ZapCard[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Card\ZapCard", mappedBy="creater")
     */
    private $zapCards;

    /**
     * @var ZapCardStockNumber[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\StockNumber\ZapCardStockNumber", mappedBy="creater")
     */
    private $stock_numbers;

    /**
     * @var Weight[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Detail\Entity\Weight\Weight", mappedBy="creater")
     */
    private $weights;

    /**
     * @var ShopPriceDealer[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Detail\Entity\Dealer\ShopPriceDealer", mappedBy="creater")
     */
    private $dealers;

    /**
     * @var ShopZamena[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Detail\Entity\Zamena\ShopZamena", mappedBy="creater", orphanRemoval=true)
     */
    private $zamena;

    /**
     * @var ShopZamena[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Detail\Entity\Zamena\ShopZamena", mappedBy="creater2", orphanRemoval=true)
     */
    private $zamena2;

    /**
     * @var DetailProviderExclude[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Detail\Entity\ProviderExclude\DetailProviderExclude", mappedBy="creater", orphanRemoval=true)
     */
    private $providerExclude;

    /**
     * @var DetailProviderPriceExclude[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Detail\Entity\PriceExclude\DetailProviderPriceExclude", mappedBy="creater", orphanRemoval=true)
     */
    private $priceExclude;

    /**
     * @var OrderGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Good\OrderGood", mappedBy="creater")
     */
    private $order_goods;

    /**
     * @var SchetGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\SchetGood\SchetGood", mappedBy="creater")
     */
    private $schet_goods;

    /**
     * @var Site[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\Order\Entity\Site\Site", mappedBy="creaters")
     */
    private $sites;


    public function __construct(string $name, ?string $name_rus, bool $isOriginal, string $tableName, ?Creater $creater_weight, ?string $description)
    {
        $this->name = $name;
        $this->name_rus = $name_rus ?: '';
        $this->isOriginal = $isOriginal;
        $this->tableName = $tableName == 'shopPriceN' ? '' : $tableName;
        $this->creater_weight = $creater_weight;
        $this->description = $description ?: '';
    }

    public function update(string $name, ?string $name_rus, bool $isOriginal, string $tableName, ?Creater $creater_weight, ?string $description, ?string $catalogs, ?string $alt_names)
    {
        $this->name = $name;
        $this->name_rus = $name_rus ?: '';
        $this->isOriginal = $isOriginal;
        $this->tableName = $tableName == 'shopPriceN' ? '' : $tableName;
        $this->creater_weight = $creater_weight;
        $this->description = $description ?: '';
        $this->catalogs = $catalogs ?: '';
        $this->alt_names = $alt_names ?: '';
    }

    public function getId(): int
    {
        return $this->createrID;
    }

    public function getCreaterWeight(): ?Creater
    {
        return $this->creater_weight;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNameRus(): ?string
    {
        return $this->name_rus;
    }

    public function getNameAvito(): ?string
    {
        return $this->name_avito == '' ? $this->name : $this->name_avito;
    }

    public function isOriginal(): ?bool
    {
        return $this->isOriginal;
    }

    public function isUSA(): ?bool
    {
        return $this->isUSA;
    }

    public function getKoefUsa()
    {
        return $this->koefUsa;
    }

    public function getTableName(): ?string
    {
        return $this->tableName ?: 'shopPriceN';
    }

    public function getTire(): ?string
    {
        return $this->tire;
    }

    public function isFast(): ?bool
    {
        return $this->isFast;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function getLogoBig(): ?string
    {
        return $this->logo_big;
    }

    public function getCatalogs(): ?string
    {
        return $this->catalogs;
    }

    public function getAltNames(): ?string
    {
        return $this->alt_names;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isHide(): ?bool
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

    public function getShopPrice1(): array
    {
        return $this->shopPrice1->toArray();
    }

    public function getShopPrice(): ?array
    {
        if ($this->tableName == 'shopPrice1') return $this->getShopPrice1();
        return null;
    }

    public function isEqual(self $other): bool
    {
        return $this->getId() === $other->getId();
    }
}
