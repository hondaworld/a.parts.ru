<?php

namespace App\Model\Income\Entity\Sklad;

use App\Model\Income\Entity\Income\Income;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IncomeSkladRepository::class)
 * @ORM\Table(name="income_sklad")
 */
class IncomeSklad
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="incomeSkladID")
     */
    private $incomeSkladID;

    /**
     * @var Income
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Income\Income", inversedBy="sklads")
     * @ORM\JoinColumn(name="incomeID", referencedColumnName="incomeID", nullable=false)
     */
    private $income;

    /**
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad", inversedBy="income_sklads")
     * @ORM\JoinColumn(name="zapSkladID", referencedColumnName="zapSkladID", nullable=false)
     */
    private $zapSklad;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity = 0;

    /**
     * @ORM\Column(type="integer", name="quantityIn")
     */
    private $quantityIn = 0;

    /**
     * @ORM\Column(type="integer", name="quantityPath")
     */
    private $quantityPath = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $reserve = 0;

    /**
     * @ORM\Column(type="integer", name="quantityReturn")
     */
    private $quantityReturn = 0;

    public function __construct(Income $income, ZapSklad $zapSklad, int $quantity)
    {
        $this->income = $income;
        $this->zapSklad = $zapSklad;
        $this->quantity = $quantity;
    }

    public static function cloneFromIncomeSklad(IncomeSklad $clone, Income $income, int $quantity): IncomeSklad
    {
        $incomeSklad = clone $clone;
        $incomeSklad->incomeSkladID = null;
        $incomeSklad->income = $income;
        $incomeSklad->changeQuantity($quantity);
        return $incomeSklad;
    }

    public function updateQuantity(int $quantity, int $quantityIn, int $quantityPath, int $reserve, int $quantityReturn)
    {
        $this->quantity = $quantity;
        $this->quantityIn = $quantityIn;
        $this->quantityPath = $quantityPath;
        $this->reserve = $reserve;
        $this->quantityReturn = $quantityReturn;
    }

    public function updateZapSklad(ZapSklad $zapSklad)
    {
        $this->zapSklad = $zapSklad;
    }

    public function changeQuantity(int $quantity)
    {
        $this->quantity = $quantity;
        if ($this->quantityPath > 0) $this->quantityPath = $quantity;
        if ($this->reserve > 0) $this->reserve = $quantity;
    }

    public function returnQuantity(int $quantity)
    {
        if ($this->quantityIn - $this->reserve < $quantity) {
            throw new \DomainException('Списываемое количество больше доступного');
        }
        $this->quantityReturn += $quantity;
        $this->quantityIn -= $quantity;
    }

    public function setQuantityPathAsQuantity()
    {
        $this->quantityPath = $this->quantity;
    }

    public function removeQuantityPath()
    {
        $this->quantityPath = 0;
    }

    public function reserveOrderGoodQuantity(OrderGood $orderGood)
    {
        $this->reserve = $orderGood->getQuantity();
    }

    public function removeReserve()
    {
        $this->reserve = 0;
    }

    public function incomeInWarehouse()
    {
        $this->quantityIn = $this->quantity;
        $this->quantityPath = 0;
    }

    public function changeReserve(int $quantity): void
    {
        $this->reserve += $quantity;
    }

    public function expense(int $quantity): void
    {
        $this->reserve -= $quantity;
        $this->quantityIn -= $quantity;
    }

    public function fromInToPath(int $quantity): void
    {
        $this->quantity -= $quantity;
        $this->quantityIn -= $quantity;
    }

    public function fromPathToIn(int $quantity): void
    {
        $this->quantityIn += $quantity;
        $this->quantityPath -= $quantity;
    }

    public function sendToPath(int $quantity): void
    {
        $this->quantity += $quantity;
        $this->quantityPath += $quantity;
    }

    public function getId(): ?int
    {
        return $this->incomeSkladID;
    }

    public function getIncome(): Income
    {
        return $this->income;
    }

    public function getZapSklad(): ZapSklad
    {
        return $this->zapSklad;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getQuantityIn(): int
    {
        return $this->quantityIn;
    }

    public function getQuantityPath(): int
    {
        return $this->quantityPath;
    }

    public function getReserve(): int
    {
        return $this->reserve;
    }

    public function getQuantityReturn(): int
    {
        return $this->quantityReturn;
    }

    public function getQuantityInWarehouse(): int
    {
        return $this->quantityIn + $this->quantityPath - $this->reserve;
    }
}
