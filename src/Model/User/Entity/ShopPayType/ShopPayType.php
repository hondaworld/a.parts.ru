<?php

namespace App\Model\User\Entity\ShopPayType;

use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShopPayTypeRepository::class)
 * @ORM\Table(name="shop_pay_types")
 */
class ShopPayType
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="shop_pay_typeID")
     */
    private $shop_pay_typeID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

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
        return $this->shop_pay_typeID;
    }

    public function getName(): ?string
    {
        return $this->name;
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

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unhide(): void
    {
        $this->isHide = false;
    }

}
