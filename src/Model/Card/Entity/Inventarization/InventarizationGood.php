<?php

namespace App\Model\Card\Entity\Inventarization;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InventarizationGoodRepository::class)
 * @ORM\Table(name="inventarization_goods")
 */
class InventarizationGood
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="goodID")
     */
    private $goodID;

    /**
     * @var Inventarization
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Inventarization\Inventarization", inversedBy="goods")
     * @ORM\JoinColumn(name="inventarizationID", referencedColumnName="inventarizationID", nullable=false)
     */
    private $inventarization;

    /**
     * @var ZapCard
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Card\ZapCard")
     * @ORM\JoinColumn(name="zapCardID", referencedColumnName="zapCardID", nullable=false)
     */
    private $zapCard;

    /**
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad")
     * @ORM\JoinColumn(name="zapSkladID", referencedColumnName="zapSkladID", nullable=false)
     */
    private $zapSklad;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $reserve = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity_real = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=false)
     */
    private $manager;

    public function __construct(Inventarization $inventarization, ZapCard $zapCard, ZapSklad $zapSklad, int $quantity, int $reserve, int $quantity_real, Manager $manager)
    {
        $this->inventarization = $inventarization;
        $this->zapCard = $zapCard;
        $this->zapSklad = $zapSklad;
        $this->quantity = $quantity;
        $this->reserve = $reserve;
        $this->quantity_real = $quantity_real;
        $this->manager = $manager;
        $this->dateofadded = new \DateTime();
    }

    public function updateQuantityReal(int $quantity_real)
    {
        $this->quantity_real = $quantity_real;
    }

    public function assignQuantityReal(int $quantity_real)
    {
        $this->quantity_real += $quantity_real;
    }

    public function getId(): int
    {
        return $this->goodID;
    }

    public function getInventarization(): Inventarization
    {
        return $this->inventarization;
    }

    public function getZapCard(): ZapCard
    {
        return $this->zapCard;
    }

    public function getZapSklad(): ZapSklad
    {
        return $this->zapSklad;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getReserve(): int
    {
        return $this->reserve;
    }

    public function getQuantityReal(): int
    {
        return $this->quantity_real;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getManager(): Manager
    {
        return $this->manager;
    }
}
