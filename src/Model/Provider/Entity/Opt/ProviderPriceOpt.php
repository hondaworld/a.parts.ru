<?php

namespace App\Model\Provider\Entity\Opt;

use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\User\Entity\Opt\Opt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProviderPriceOptRepository::class)
 * @ORM\Table(name="linkOpt")
 */
class ProviderPriceOpt
{
    /**
     * @ORM\Id
     * @var Opt
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\Opt\Opt", inversedBy="provider_price_profits")
     * @ORM\JoinColumn(name="optID", referencedColumnName="optID", nullable=false)
     */
    private $opt;

    /**
     * @ORM\Id
     * @var ProviderPrice
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\Price\ProviderPrice", inversedBy="profits")
     * @ORM\JoinColumn(name="providerPriceID", referencedColumnName="providerPriceID", nullable=false)
     */
    private $providerPrice;

    /**
     * @ORM\Column(type="koef", precision=5, scale=2)
     */
    private $profit;

    public function __construct(ProviderPrice $providerPrice, Opt $opt, string $profit)
    {
        $this->opt = $opt;
        $this->providerPrice = $providerPrice;
        $this->profit = $profit;
    }

    public function getOpt(): Opt
    {
        return $this->opt;
    }

    public function getProviderPrice(): ProviderPrice
    {
        return $this->providerPrice;
    }

    public function getProfit(): ?string
    {
        return $this->profit;
    }
}
