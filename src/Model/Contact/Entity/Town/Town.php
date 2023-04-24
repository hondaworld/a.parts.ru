<?php

namespace App\Model\Contact\Entity\Town;

use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Contact\Entity\TownRegion\TownRegion;
use App\Model\Contact\Entity\TownType\TownType;
use App\Model\User\Entity\FirmContr\FirmContr;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TownRepository::class)
 * @ORM\Table(name="towns")
 */
class Town
{
    public const MSK_ID = 598;
    public const SPB_ID = 822;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="townID")
     */
    private $townID;

    /**
     * @var TownRegion
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\TownRegion\TownRegion", inversedBy="towns")
     * @ORM\JoinColumn(name="regionID", referencedColumnName="regionID", nullable=false)
     */
    private $region;

    /**
     * @var TownType
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\TownType\TownType", inversedBy="towns")
     * @ORM\JoinColumn(name="typeID", referencedColumnName="id", nullable=false)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name_short;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name_doc;

    /**
     * @ORM\Column(type="smallint", name="daysFromMoscow")
     */
    private $daysFromMoscow;

    /**
     * @ORM\Column(type="boolean", name="isFree")
     */
    private $isFree = false;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var Contact[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Contact\Entity\Contact\Contact", mappedBy="town")
     */
    private $contacts;

    /**
     * @var User[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\User\User", mappedBy="town")
     */
    private $users;

    /**
     * @var FirmContr[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\FirmContr\FirmContr", mappedBy="town")
     */
    private $firmcontr;

    public function __construct(TownRegion $region, TownType $type, string $name, string $name_short, ?string $name_doc, int $daysFromMoscow, bool $isFree)
    {
        $this->region = $region;
        $this->type = $type;
        $this->name = $name;
        $this->name_short = $name_short;
        $this->name_doc = $name_doc ?: '';
        $this->daysFromMoscow = $daysFromMoscow;
        $this->isFree = $isFree;
    }

    public function update(TownRegion $region, TownType $type, string $name, string $name_short, ?string $name_doc, int $daysFromMoscow, bool $isFree)
    {
        $this->region = $region;
        $this->type = $type;
        $this->name = $name;
        $this->name_short = $name_short;
        $this->name_doc = $name_doc ?: '';
        $this->daysFromMoscow = $daysFromMoscow;
        $this->isFree = $isFree;
    }

    public function getId(): ?int
    {
        return $this->townID;
    }

    public function getRegion(): TownRegion
    {
        return $this->region;
    }

    public function getType(): TownType
    {
        return $this->type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNameShort(): ?string
    {
        return $this->name_short;
    }

    public function getNameDoc(): ?string
    {
        return $this->name_doc;
    }

    public function getDaysFromMoscow(): ?int
    {
        return $this->daysFromMoscow;
    }

    public function getIsFree(): ?bool
    {
        return $this->isFree;
    }

    public function getIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function getIsHide(): ?bool
    {
        return $this->isHide;
    }

    public function getContacts(): array
    {
        return $this->contacts->toArray();
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unhide(): void
    {
        $this->isHide = false;
    }

    public function getUsers(): array
    {
        return $this->users->toArray();
    }

    public function getNameWithRegion(): string
    {
        $region = $this->region->getName();
        if ($region != $this->name) return $region . ', ' . $this->name;
        return $this->name;
    }

    public function isMsk(): bool
    {
        return $this->townID == self::MSK_ID;
    }

    public function isSpb(): bool
    {
        return $this->townID == self::SPB_ID;
    }
}
