<?php

namespace App\Model\Card\Entity\Location;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Shop\Entity\Location\ShopLocation;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZapSkladLocationRepository::class)
 * @ORM\Table(name="zapSkladLocation")
 */
class ZapSkladLocation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="zapSkladLocationID")
     */
    private $zapSkladLocationID;

    /**
     * @var ZapCard
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Card\ZapCard", inversedBy="locations")
     * @ORM\JoinColumn(name="zapCardID", referencedColumnName="zapCardID", nullable=false)
     */
    private $zapCard;

    /**
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad", inversedBy="locations")
     * @ORM\JoinColumn(name="zapSkladID", referencedColumnName="zapSkladID", nullable=false)
     */
    private $zapSklad;

    /**
     * @var ShopLocation
     * @ORM\ManyToOne(targetEntity="App\Model\Shop\Entity\Location\ShopLocation", inversedBy="zapSkladLocations")
     * @ORM\JoinColumn(name="locationID", referencedColumnName="locationID", nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="integer", name="quantityMin")
     */
    private $quantityMin = 0;

    /**
     * @ORM\Column(type="boolean", name="quantityMinIsReal")
     */
    private $quantityMinIsReal = false;

    /**
     * @ORM\Column(type="integer", name="quantityMax")
     */
    private $quantityMax = 0;

    public function __construct(ZapCard $zapCard, ZapSklad $zapSklad, ?ShopLocation $location = null, int $quantityMin = 0, bool $quantityMinIsReal = false, int $quantityMax = 0)
    {
        $this->zapCard = $zapCard;
        $this->zapSklad = $zapSklad;
        $this->location = $location;
        $this->quantityMin = $quantityMin;
        $this->quantityMinIsReal = $quantityMinIsReal;
        $this->quantityMax = $quantityMax;
    }

    public function update(?ShopLocation $location = null, int $quantityMin = 0, bool $quantityMinIsReal = false, int $quantityMax = 0)
    {
        $this->location = $location;
        $this->quantityMin = $quantityMin;
        $this->quantityMinIsReal = $quantityMinIsReal;
        $this->quantityMax = $quantityMax;
    }

    public function updateQuantityMin(?int $quantityMin = 0)
    {
        $this->quantityMin = $quantityMin ?: 0;
    }

    public function updateQuantityMax(?int $quantityMax = 0)
    {
        $this->quantityMax = $quantityMax ?: 0;
    }

    public function updateShopLocation(?ShopLocation $location = null)
    {
        $this->location = $location;
    }

    public function getId(): ?int
    {
        return $this->zapSkladLocationID;
    }

    public function getZapCard(): ZapCard
    {
        return $this->zapCard;
    }

    public function getZapSklad(): ZapSklad
    {
        return $this->zapSklad;
    }

    public function getLocation(): ?ShopLocation
    {
        return $this->location;
    }

    public function getQuantityMin(): int
    {
        return $this->quantityMin;
    }

    public function getQuantityMinIsReal(): bool
    {
        return $this->quantityMinIsReal;
    }

    public function getQuantityMax(): int
    {
        return $this->quantityMax;
    }
}
