<?php

namespace App\Model\Card\Entity\Stock;

use App\Model\Card\Entity\StockNumber\ZapCardStockNumber;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Provider\Entity\Provider\Provider;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZapCardStockRepository::class)
 * @ORM\Table(name="zapCardStocks")
 */
class ZapCardStock
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="stockID")
     */
    private $stockID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @ORM\Column(type="date")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @var Provider[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Provider\Entity\Provider\Provider", inversedBy="stocks")
     * @ORM\JoinTable(name="zapCardStock_providers",
     *      joinColumns={@ORM\JoinColumn(name="stockID", referencedColumnName="stockID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="providerID", referencedColumnName="providerID")}
     * )
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $providers;

    /**
     * @var ZapCardStockNumber[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\StockNumber\ZapCardStockNumber", mappedBy="stock", orphanRemoval=true)
     */
    private $stock_numbers;

    /**
     * @var OrderGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Good\OrderGood", mappedBy="stock")
     */
    private $order_goods;

    public function __construct(string $name, ?string $text)
    {
        $this->name = $name;
        $this->text = $text ?: '';
        $this->dateofadded = new \DateTime();
        $this->providers = new ArrayCollection();
    }

    public function update(string $name, ?string $text, \DateTime $dateofadded)
    {
        $this->name = $name;
        $this->text = $text ?: '';
        $this->dateofadded = $dateofadded;
    }

    public function getId(): ?int
    {
        return $this->stockID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
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
     * @return Provider[]|ArrayCollection
     */
    public function getProviders()
    {
        return $this->providers->toArray();
    }

    public function cleaProviders(): void
    {
        $this->providers->clear();
    }

    /**
     * @param Provider $provider
     */
    public function assignProvider(Provider $provider): void
    {
        $this->providers->add($provider);
    }
}
