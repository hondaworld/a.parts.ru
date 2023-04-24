<?php

namespace App\Model\Card\Entity\StockNumber;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Stock\ZapCardStock;
use App\Model\Detail\Entity\Creater\Creater;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZapCardStockNumberRepository::class)
 * @ORM\Table(name="zapCardStock_numbers")
 */
class ZapCardStockNumber
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="numberID")
     */
    private $numberID;

    /**
     * @var ZapCardStock
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Stock\ZapCardStock", inversedBy="stock_numbers")
     * @ORM\JoinColumn(name="stockID", referencedColumnName="stockID", nullable=false)
     */
    private $stock;

    /**
     * @var DetailNumber
     * @ORM\Column(type="detail_number", length=30)
     */
    private $number;

    /**
     * @var Creater
     * @ORM\ManyToOne(targetEntity="App\Model\Detail\Entity\Creater\Creater", inversedBy="stock_numbers", fetch="EAGER")
     * @ORM\JoinColumn(name="createrID", referencedColumnName="createrID", nullable=false)
     */
    private $creater;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $price_stock;

    public function __construct(ZapCardStock $zapCardStock, DetailNumber $number, Creater $creater, ?string $price_stock)
    {
        $this->stock = $zapCardStock;
        $this->number = $number;
        $this->creater = $creater;
        $this->price_stock = $price_stock;
    }

    public function update(ZapCardStock $zapCardStock, ?string $price_stock)
    {
        $this->stock = $zapCardStock;
        $this->price_stock = $price_stock;
    }

    public function updatePriceStock(?string $price_stock)
    {
        $this->price_stock = $price_stock;
    }

    public function getId(): ?int
    {
        return $this->numberID;
    }

    public function getStock(): ZapCardStock
    {
        return $this->stock;
    }

    public function getNumber(): DetailNumber
    {
        return $this->number;
    }

    public function getCreater(): Creater
    {
        return $this->creater;
    }

    public function getPriceStock(): ?string
    {
        return $this->price_stock;
    }
}
