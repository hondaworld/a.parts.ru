<?php

namespace App\Model\Shop\Entity\Location;

use App\Model\Card\Entity\Location\ZapSkladLocation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShopLocationRepository::class)
 * @ORM\Table(name="shopLocation")
 */
class ShopLocation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="locationID")
     */
    private $locationID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $name_short;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var ZapSkladLocation[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Location\ZapSkladLocation", mappedBy="location")
     */
    private $zapSkladLocations;

    public function __construct(string $name, string $name_short)
    {
        $this->name = $name;
        $this->name_short = $name_short;
    }

    public function update(string $name, string $name_short)
    {
        $this->name = $name;
        $this->name_short = $name_short;
    }

    public function getId(): int
    {
        return $this->locationID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNameShort(): ?string
    {
        return $this->name_short;
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

    /**
     * @return ZapSkladLocation[]|ArrayCollection
     */
    public function getZapSkladLocations()
    {
        return $this->zapSkladLocations->toArray();
    }


}
