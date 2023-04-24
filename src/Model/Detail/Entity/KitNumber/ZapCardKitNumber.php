<?php

namespace App\Model\Detail\Entity\KitNumber;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Kit\ZapCardKit;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZapCardKitNumberRepository::class)
 * @ORM\Table(name="zapCardKitNumbers")
 */
class ZapCardKitNumber
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var ZapCardKit
     * @ORM\ManyToOne(targetEntity="App\Model\Detail\Entity\Kit\ZapCardKit", inversedBy="numbers")
     * @ORM\JoinColumn(name="zap_card_kit_id", referencedColumnName="id", nullable=false)
     */
    private $kit;

    /**
     * @var DetailNumber
     * @ORM\Column(type="detail_number", length=30)
     */
    private $number;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    public function __construct(ZapCardKit $kit, DetailNumber $number, int $quantity, int $sort)
    {
        $this->kit = $kit;
        $this->number = $number;
        $this->quantity = $quantity;
        $this->sort = $sort;
    }

    public function update(DetailNumber $number, int $quantity)
    {
        $this->number = $number;
        $this->quantity = $quantity;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKit(): ZapCardKit
    {
        return $this->kit;
    }

    public function getNumber(): DetailNumber
    {
        return $this->number;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function isHide(): bool
    {
        return $this->isHide;
    }

    public function changeSort(int $sort): void
    {
        $this->sort = $sort;
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unHide(): void
    {
        $this->isHide = false;
    }
}
