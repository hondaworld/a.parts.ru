<?php

namespace App\Model\Auto\Entity\MotoModel;

use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Auto\Entity\Moto\Moto;
use App\Model\Auto\Entity\MotoGroup\MotoGroup;
use App\Model\Card\Entity\Auto\ZapCardAuto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MotoModelRepository::class)
 * @ORM\Table(name="moto_model")
 */
class MotoModel
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="moto_modelID")
     */
    private $moto_modelID;

    /**
     * @var AutoMarka
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\Marka\AutoMarka", inversedBy="moto_models")
     * @ORM\JoinColumn(name="auto_markaID", referencedColumnName="auto_markaID", nullable=false)
     */
    private $marka;

    /**
     * @var MotoGroup
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\MotoGroup\MotoGroup", inversedBy="moto_models")
     * @ORM\JoinColumn(name="moto_groupID", referencedColumnName="moto_groupID", nullable=false)
     */
    private $group;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var Description
     * @ORM\Embedded(class="Description", columnPrefix="description_")
     */
    private $description;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var Moto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Auto\Entity\Moto\Moto", mappedBy="model")
     */
    private $motos;

    /**
     * @var ZapCardAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Auto\ZapCardAuto", mappedBy="moto_model")
     */
    private $zapCard_auto;

    public function __construct(AutoMarka $marka, MotoGroup $group, string $name)
    {
        $this->marka = $marka;
        $this->group = $group;
        $this->name = $name;
        $this->description = new Description();
    }

    public function update(MotoGroup $group, string $name)
    {
        $this->group = $group;
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->moto_modelID;
    }

    public function getMarka(): AutoMarka
    {
        return $this->marka;
    }

    public function getGroup(): MotoGroup
    {
        return $this->group;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): Description
    {
        return $this->description;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unHide(): void
    {
        $this->isHide = false;
    }

    /**
     * @return Moto[]|ArrayCollection
     */
    public function getMotos()
    {
        return $this->motos->toArray();
    }
}
