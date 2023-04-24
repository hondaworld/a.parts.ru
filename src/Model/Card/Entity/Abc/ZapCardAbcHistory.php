<?php

namespace App\Model\Card\Entity\Abc;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZapCardAbcHistoryRepository::class)
 * @ORM\Table(name="zapCard_abc_history")
 */
class ZapCardAbcHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var ZapCard
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Card\ZapCard", inversedBy="abc_history", fetch="EAGER")
     * @ORM\JoinColumn(name="zapCardID", referencedColumnName="zapCardID")
     */
    private $zapCard;

    /**
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad", inversedBy="abc_history", fetch="EAGER")
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

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID")
     */
    private $manager;

    public function __construct(ZapCard $zapCard, ZapSklad $zapSklad, string $abc, Manager $manager)
    {
        $this->zapCard = $zapCard;
        $this->zapSklad = $zapSklad;
        $this->abc = $abc;
        $this->dateofadded = new \DateTime();
        $this->manager = $manager;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
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

    /**
     * @return Manager
     */
    public function getManager(): Manager
    {
        return $this->manager;
    }


}
