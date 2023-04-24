<?php

namespace App\Model\Card\Entity\Opt;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\User\Entity\Opt\Opt;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZapCardOptRepository::class)
 * @ORM\Table(name="linkZapCard")
 */
class ZapCardOpt
{
    /**
     * @ORM\Id
     * @var Opt
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\Opt\Opt", inversedBy="zapCard_profits", fetch="EAGER")
     * @ORM\JoinColumn(name="optID", referencedColumnName="optID", nullable=false)
     */
    private $opt;

    /**
     * @ORM\Id
     * @var ZapCard
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Card\ZapCard", inversedBy="profits")
     * @ORM\JoinColumn(name="zapCardID", referencedColumnName="zapCardID", nullable=false)
     */
    private $zapCard;

    /**
     * @ORM\Column(type="koef", precision=6, scale=2)
     */
    private $profit;

    public function __construct(ZapCard $zapCard, Opt $opt, string $profit)
    {
        $this->opt = $opt;
        $this->zapCard = $zapCard;
        $this->profit = $profit;
    }

    /**
     * @return Opt
     */
    public function getOpt(): Opt
    {
        return $this->opt;
    }

    /**
     * @return ZapCard
     */
    public function getZapCard(): ZapCard
    {
        return $this->zapCard;
    }


    public function getProfit(): ?string
    {
        return $this->profit;
    }
}
