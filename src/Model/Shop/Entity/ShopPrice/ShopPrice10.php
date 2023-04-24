<?php

namespace App\Model\Shop\Entity\ShopPrice;

use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Provider\Entity\Price\ProviderPrice;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShopPrice10Repository::class)
 * @ORM\Table(name="shopPrice1")
 */
class ShopPrice10
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=30)
     */
    private $number;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=2)
     */
    private $price;

    /**
     * @ORM\Column(type="datetime", name="dateOfChanged")
     */
    private $dateOfChanged;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Id
     * @var Creater
     * @ORM\ManyToOne(targetEntity="App\Model\Detail\Entity\Creater\Creater", inversedBy="shopPrice10")
     * @ORM\JoinColumn(name="createrID", referencedColumnName="createrID", nullable=false)
     */
    private $creater;

    /**
     * @ORM\Id
     * @var ProviderPrice
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\Price\ProviderPrice", inversedBy="shopPrice10")
     * @ORM\JoinColumn(name="providerPriceID", referencedColumnName="providerPriceID", nullable=false)
     */
    private $providerPrice;

    public function __construct(string $number, float $price, int $quantity, string $name, ProviderPrice $providerPrice, Creater $creater)
    {
        $this->number = $number;
        $this->price = $price;
        $this->quantity = $quantity;
        $this->name = $name;
        $this->providerPrice = $providerPrice;
        $this->creater = $creater;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function getProviderPrice(): ProviderPrice
    {
        return $this->providerPrice;
    }

    public function getCreater(): Creater
    {
        return $this->creater;
    }

    public function getDateOfChanged(): ?\DateTimeInterface
    {
        return $this->dateOfChanged;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
