<?php

namespace App\Model\Contact\Entity\TownRegion;

use App\Model\Contact\Entity\Country\Country;
use App\Model\Contact\Entity\Town\Town;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TownRegionRepository::class)
 * @ORM\Table(name="townRegions")
 */
class TownRegion
{
    public const MSK = 79;
    public const SPB = 78;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="regionID")
     */
    private $regionID;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @ORM\Column(type="smallint", name="daysFromMoscow")
     */
    private $daysFromMoscow;

    /**
     * @var Country
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Country\Country", inversedBy="regions")
     * @ORM\JoinColumn(name="countryID", referencedColumnName="countryID", nullable=false)
     */
    private $country;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @var Town[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Contact\Entity\Town\Town", mappedBy="region")
     */
    private $towns;

    public function __construct(Country $country, string $name, int $daysFromMoscow)
    {
        $this->country = $country;
        $this->name = $name;
        $this->daysFromMoscow = $daysFromMoscow;
    }

    public function update(Country $country, string $name, int $daysFromMoscow)
    {
        $this->country = $country;
        $this->name = $name;
        $this->daysFromMoscow = $daysFromMoscow;
    }

    public function getId(): ?int
    {
        return $this->regionID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDaysFromMoscow(): ?int
    {
        return $this->daysFromMoscow;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function getIsHide(): ?bool
    {
        return $this->isHide;
    }

    public function getIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function getTowns(): array
    {
        return $this->towns->toArray();
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unhide(): void
    {
        $this->isHide = false;
    }
}
