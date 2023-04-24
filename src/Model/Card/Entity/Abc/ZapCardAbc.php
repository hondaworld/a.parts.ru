<?php

namespace App\Model\Card\Entity\Abc;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZapCardAbcRepository::class)
 * @ORM\Table(name="zapCard_abc")
 */
class ZapCardAbc
{
    /**
     * @ORM\Id
     * @var ZapCard
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Card\ZapCard", inversedBy="abc", fetch="EAGER")
     * @ORM\JoinColumn(name="zapCardID", referencedColumnName="zapCardID")
     */
    private $zapCard;

    /**
     * @ORM\Id
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad", inversedBy="abc", fetch="EAGER")
     * @ORM\JoinColumn(name="zapSkladID", referencedColumnName="zapSkladID")
     */
    private $zapSklad;

    /**
     * @ORM\Column(type="string", length=2)
     */
    private $abc;

    /**
     * @ORM\Column(type="date")
     */
    private $dateofadded;

    public function __construct(ZapCard $zapCard, ZapSklad $zapSklad, string $abc)
    {
        $this->zapCard = $zapCard;
        $this->zapSklad = $zapSklad;
        $this->abc = $abc;
        $this->dateofadded = new \DateTime();
    }

    public function update(string $abc)
    {
        $this->abc = $abc;
        $this->dateofadded = new \DateTime();
    }

    /**
     * @return ZapCard
     */
    public function getZapCard(): ZapCard
    {
        return $this->zapCard;
    }

    /**
     * @return ZapSklad
     */
    public function getZapSklad(): ZapSklad
    {
        return $this->zapSklad;
    }

    /**
     * @return mixed
     */
    public function getAbc()
    {
        return $this->abc;
    }

    /**
     * @return mixed
     */
    public function getDateofadded()
    {
        return $this->dateofadded;
    }


}
