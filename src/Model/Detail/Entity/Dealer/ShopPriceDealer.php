<?php

namespace App\Model\Detail\Entity\Dealer;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShopPriceDealerRepository::class)
 * @ORM\Table(name="shopPriceDealer")
 */
class ShopPriceDealer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="shopPriceDealerID")
     */
    private $shopPriceDealerID;

    /**
     * @var DetailNumber
     * @ORM\Column(type="detail_number", length=30)
     */
    private $number;

    /**
     * @var Creater
     * @ORM\ManyToOne(targetEntity="App\Model\Detail\Entity\Creater\Creater", inversedBy="dealers")
     * @ORM\JoinColumn(name="createrID", referencedColumnName="createrID", nullable=false)
     */
    private $creater;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $price;

    public function __construct(DetailNumber $number, Creater $creater, string $price)
    {
        $this->number = $number;
        $this->creater = $creater;
        $this->price = $price;
    }

    public function update(string $price)
    {
        $this->price = $price;
    }

    public function getId(): ?int
    {
        return $this->shopPriceDealerID;
    }

    public function getNumber(): DetailNumber
    {
        return $this->number;
    }

    public function getCreater(): Creater
    {
        return $this->creater;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }
}
