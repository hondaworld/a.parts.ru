<?php

namespace App\Model\Card\Entity\Measure;

use App\Model\Card\Entity\Card\ZapCard;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EdIzmRepository::class)
 * @ORM\Table(name="ed_izm")
 */
class EdIzm
{
    public const DEFAULT_ID = 12;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="ed_izmID")
     */
    private $ed_izmID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $name_short;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $okei;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var ZapCard[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Card\ZapCard", mappedBy="ed_izm")
     */
    private $zapCards;

    public function __construct(string $name, string $name_short, string $okei)
    {
        $this->name = $name;
        $this->name_short = $name_short;
        $this->okei = $okei;
    }

    public function update(string $name, string $name_short, string $okei)
    {
        $this->name = $name;
        $this->name_short = $name_short;
        $this->okei = $okei;
    }

    public function getId(): ?int
    {
        return $this->ed_izmID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNameShort(): ?string
    {
        return $this->name_short;
    }

    public function getOkei(): ?string
    {
        return $this->okei;
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
     * @return ZapCard[]|array
     */
    public function getZapCards(): array
    {
        return $this->zapCards->toArray();
    }


}
