<?php

namespace App\Model\Sklad\Entity\PriceList;

use App\Model\Sklad\Entity\Opt\PriceListOpt;
use App\Model\Sklad\Entity\PriceGroup\PriceGroup;
use App\Model\User\Entity\Opt\Opt;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PriceListRepository::class)
 * @ORM\Table(name="price_lists")
 */
class PriceList
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="price_listID")
     */
    private $price_listID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="koef", precision=5, scale=2)
     */
    private $koef_dealer;

    /**
     * @ORM\Column(type="boolean")
     */
    private $no_discount;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @ORM\Column(type="boolean", name="noneDelete")
     */
    private $noneDelete = false;

    /**
     * @var PriceGroup[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Sklad\Entity\PriceGroup\PriceGroup", mappedBy="price_list", orphanRemoval=true)
     */
    private $price_groups;

    /**
     * @var PriceListOpt[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Sklad\Entity\Opt\PriceListOpt", cascade={"persist"}, mappedBy="price_list", orphanRemoval=true)
     */
    private $profits;

    public function __construct(string $name, ?string $koef_dealer, bool $no_discount, bool $isMain)
    {
        $this->name = $name;
        $this->koef_dealer = $koef_dealer;
        $this->no_discount = $no_discount;
        $this->isMain = $isMain;
        $this->profits = new ArrayCollection();
    }

    public function update(string $name, ?string $koef_dealer, bool $no_discount, bool $isMain)
    {
        $this->name = $name;
        $this->koef_dealer = $koef_dealer;
        $this->no_discount = $no_discount;
        $this->isMain = $isMain;
    }

    public function assignProfit(Opt $opt, string $profit)
    {
        $this->profits->add(new PriceListOpt($this, $opt, $profit));
    }

    public function clearProfits()
    {
        $this->profits->clear();
    }

    public function getId(): ?int
    {
        return $this->price_listID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getKoefDealer(): ?string
    {
        return $this->koef_dealer;
    }

    public function getNoDiscount(): ?bool
    {
        return $this->no_discount;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
    }

    public function getNoneDelete(): ?bool
    {
        return $this->noneDelete;
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
     * @return PriceGroup[]|array
     */
    public function getPriceGroups(): array
    {
        return $this->price_groups->toArray();
    }

    /**
     * @return PriceListOpt[]|array
     */
    public function getProfits(): array
    {
        return $this->profits->toArray();
    }

    public function clearZapCards()
    {
        foreach ($this->price_groups as $priceGroup) {
            foreach ($priceGroup->getZapCards() as $zapCard) {
                $zapCard->removePriceGroup();
            }
        }
    }
}
