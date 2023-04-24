<?php

namespace App\Model\Detail\Entity\PriceExclude;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Provider\Entity\Price\ProviderPrice;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DetailProviderPriceExcludeRepository::class)
 * @ORM\Table(name="numberPricesExclude")
 */
class DetailProviderPriceExclude
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="excludeID")
     */
    private $excludeID;

    /**
     * @var DetailNumber
     * @ORM\Column(type="detail_number", length=30)
     */
    private $number;

    /**
     * @var Creater
     * @ORM\ManyToOne(targetEntity="App\Model\Detail\Entity\Creater\Creater", inversedBy="priceExclude")
     * @ORM\JoinColumn(name="createrID", referencedColumnName="createrID", nullable=false)
     */
    private $creater;

    /**
     * @var ProviderPrice
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\Price\ProviderPrice", inversedBy="priceExclude")
     * @ORM\JoinColumn(name="providerPriceID", referencedColumnName="providerPriceID", nullable=false)
     */
    private $providerPrice;

    public function __construct(DetailNumber $number, Creater $creater, ProviderPrice $providerPrice)
    {
        $this->number = $number;
        $this->creater = $creater;
        $this->providerPrice = $providerPrice;
    }

    public function getId(): ?int
    {
        return $this->excludeID;
    }

    public function getNumber(): DetailNumber
    {
        return $this->number;
    }

    public function getCreater(): Creater
    {
        return $this->creater;
    }

    public function getProviderPrice(): ProviderPrice
    {
        return $this->providerPrice;
    }
}
