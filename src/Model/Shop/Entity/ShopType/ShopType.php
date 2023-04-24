<?php

namespace App\Model\Shop\Entity\ShopType;

use App\Model\Card\Entity\Card\ZapCard;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShopTypeRepository::class)
 * @ORM\Table(name="shop_types")
 */
class ShopType
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="shop_typeID")
     */
    private $shop_typeID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="noneDelete")
     */
    private $noneDelete = false;

    /**
     * @var ZapCard[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Card\ZapCard", mappedBy="shop_type")
     */
    private $zapCards;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function update(string $name)
    {
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->shop_typeID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    public function isNoneDelete(): ?bool
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
     * @return ZapCard[]|array
     */
    public function getZapCards(): array
    {
        return $this->zapCards->toArray();
    }


}
