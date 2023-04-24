<?php

namespace App\Model\Sklad\Entity\PriceGroup;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Sklad\Entity\PriceList\PriceList;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PriceGroupRepository::class)
 * @ORM\Table(name="price_groups")
 */
class PriceGroup
{
    public const DEFAULT_ID = 1;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="price_groupID")
     */
    private $price_groupID;

    /**
     * @var PriceList
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\PriceList\PriceList", inversedBy="price_groups")
     * @ORM\JoinColumn(name="price_listID", referencedColumnName="price_listID", nullable=false)
     */
    private $price_list;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="noneDelete")
     */
    private $noneDelete = false;

    /**
     * @var ZapCard[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Card\ZapCard", mappedBy="price_group")
     */
    private $zapCards;

    public function __construct(PriceList $price_list, string $name, bool $isMain)
    {
        $this->price_list = $price_list;
        $this->name = $name;
        $this->isMain = $isMain;
    }

    public function update(string $name, bool $isMain)
    {
        $this->name = $name;
        $this->isMain = $isMain;
    }

    public function getId(): ?int
    {
        return $this->price_groupID;
    }

    public function getName(): ?string
    {
        return $this->name;
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
     * @return PriceList
     */
    public function getPriceList(): PriceList
    {
        return $this->price_list;
    }

    /**
     * @return ZapCard[]|array
     */
    public function getZapCards(): array
    {
        return $this->zapCards->toArray();
    }



    public function clearZapCards()
    {
        foreach ($this->zapCards as $zapCard) {
            $zapCard->removePriceGroup();
        }
    }

}
