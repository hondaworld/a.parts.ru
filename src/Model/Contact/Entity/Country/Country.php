<?php

namespace App\Model\Contact\Entity\Country;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Contact\Entity\TownRegion\TownRegion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CountryRepository::class)
 * @ORM\Table(name="countries")
 */
class Country
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="countryID")
     */
    private $countryID;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=3)
     */
    private $code;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="noneDelete")
     */
    private $noneDelete = false;

    /**
     * @ORM\Column(type="boolean", name="isContact")
     */
    private $isContact = false;

    /**
     * @var TownRegion[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Contact\Entity\TownRegion\TownRegion", mappedBy="country")
     */
    private $regions;

    /**
     * @var ZapCard[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Card\ZapCard", mappedBy="country")
     */
    private $zapCards;

    public function __construct(string $name, string $code)
    {
        $this->name = $name;
        $this->code = $code;
    }

    public function update(string $name, string $code)
    {
        $this->name = $name;
        $this->code = $code;
    }

    public function getId(): ?int
    {
        return $this->countryID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function getIsHide(): ?bool
    {
        return $this->isHide;
    }

    public function getNoneDelete(): ?bool
    {
        return $this->noneDelete;
    }

    public function getIsContact(): ?bool
    {
        return $this->isContact;
    }

    public function getRegions(): array
    {
        return $this->regions->toArray();
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
