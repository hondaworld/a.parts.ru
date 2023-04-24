<?php

namespace App\Model\Sklad\Entity\Opt;

use App\Model\Sklad\Entity\PriceList\PriceList;
use App\Model\User\Entity\Opt\Opt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PriceListOptRepository::class)
 * @ORM\Table(name="linkPrice_list")
 */
class PriceListOpt
{
    /**
     * @ORM\Id
     * @var Opt
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\Opt\Opt", inversedBy="price_list_profits", fetch="EAGER")
     * @ORM\JoinColumn(name="optID", referencedColumnName="optID", nullable=false)
     */
    private $opt;

    /**
     * @ORM\Id
     * @var PriceList
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\PriceList\PriceList", inversedBy="profits", fetch="EAGER")
     * @ORM\JoinColumn(name="price_listID", referencedColumnName="price_listID", nullable=false)
     */
    private $price_list;

    /**
     * @ORM\Column(type="koef", precision=5, scale=2)
     */
    private $profit;

    public function __construct(PriceList $priceList, Opt $opt, string $profit)
    {
        $this->opt = $opt;
        $this->price_list = $priceList;
        $this->profit = $profit;
    }

    public function getOpt(): Opt
    {
        return $this->opt;
    }

    public function getPriceList(): PriceList
    {
        return $this->price_list;
    }

    public function getProfit(): ?string
    {
        return $this->profit;
    }
}
