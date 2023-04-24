<?php

namespace App\Model\Card\Entity\Auto;

use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Auto\Entity\MotoModel\MotoModel;
use App\Model\Card\Entity\Card\ZapCard;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ZapCardAutoRepository::class)
 * @ORM\Table(name="zapCard_auto")
 */
class ZapCardAuto
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="zapCard_autoID")
     */
    private $zapCard_autoID;

    /**
     * @var ZapCard
     * @ORM\ManyToOne(targetEntity="App\Model\Card\Entity\Card\ZapCard", inversedBy="autos", fetch="EAGER")
     * @ORM\JoinColumn(name="zapCardID", referencedColumnName="zapCardID", nullable=false)
     */
    private $zapCard;

    /**
     * @var AutoModel
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\Model\AutoModel", inversedBy="zapCard_auto", fetch="EAGER")
     * @ORM\JoinColumn(name="auto_modelID", referencedColumnName="auto_modelID", nullable=true)
     */
    private $auto_model;

    /**
     * @var MotoModel
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\MotoModel\MotoModel", inversedBy="zapCard_auto", fetch="EAGER")
     * @ORM\JoinColumn(name="moto_modelID", referencedColumnName="moto_modelID", nullable=true)
     */
    private $moto_model;

    /**
     * @ORM\Column(type="integer")
     */
    private $year;

    public function __construct(ZapCard $zapCard, ?AutoModel $auto_model, ?MotoModel $moto_model, int $year)
    {
        $this->zapCard = $zapCard;
        $this->auto_model = $auto_model;
        $this->moto_model = $moto_model;
        $this->year = $year;
    }

    public function getId(): ?int
    {
        return $this->zapCard_autoID;
    }

    public function getZapCard(): ZapCard
    {
        return $this->zapCard;
    }

    public function getAutoModel(): ?AutoModel
    {
        return $this->auto_model;
    }

    public function getMotoModel(): ?MotoModel
    {
        return $this->moto_model;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }
}
