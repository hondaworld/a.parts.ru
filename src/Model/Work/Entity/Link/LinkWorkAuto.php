<?php

namespace App\Model\Work\Entity\Link;

use App\Model\Auto\Entity\Generation\AutoGeneration;
use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Auto\Entity\Modification\AutoModification;
use App\Model\Work\Entity\Group\WorkGroup;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LinkWorkAutoRepository::class)
 * @ORM\Table(name="linkWorkAuto")
 */
class LinkWorkAuto
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var WorkGroup
     * @ORM\ManyToOne(targetEntity="App\Model\Work\Entity\Group\WorkGroup", inversedBy="autos", fetch="EAGER")
     * @ORM\JoinColumn(name="workGroupID", referencedColumnName="workGroupID")
     */
    private $workGroup;

    /**
     * @var AutoMarka
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\Marka\AutoMarka", inversedBy="work_autos", fetch="EAGER")
     * @ORM\JoinColumn(name="auto_markaID", referencedColumnName="auto_markaID", nullable=true)
     */
    private $auto_marka;

    /**
     * @var AutoModel
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\Model\AutoModel", inversedBy="work_autos", fetch="EAGER")
     * @ORM\JoinColumn(name="auto_modelID", referencedColumnName="auto_modelID", nullable=true)
     */
    private $auto_model;

    /**
     * @var AutoGeneration
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\Generation\AutoGeneration", inversedBy="work_autos", fetch="EAGER")
     * @ORM\JoinColumn(name="auto_generationID", referencedColumnName="auto_generationID", nullable=true)
     */
    private $auto_generation;

    /**
     * @var AutoModification
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\Modification\AutoModification", inversedBy="work_autos", fetch="EAGER")
     * @ORM\JoinColumn(name="auto_modificationID", referencedColumnName="auto_modificationID", nullable=true)
     */
    private $auto_modification;

    public function __construct(WorkGroup $workGroup, ?AutoMarka $autoMarka, ?AutoModel $autoModel, ?AutoGeneration $autoGeneration, ?AutoModification $autoModification)
    {
        $this->workGroup = $workGroup;
        $this->auto_marka = $autoMarka;
        $this->auto_model = $autoModel;
        $this->auto_generation = $autoGeneration;
        $this->auto_modification = $autoModification;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getWorkGroup(): WorkGroup
    {
        return $this->workGroup;
    }

    public function getAutoMarka(): ?AutoMarka
    {
        return $this->auto_marka;
    }

    public function getAutoModel(): ?AutoModel
    {
        return $this->auto_model;
    }

    public function getAutoGeneration(): ?AutoGeneration
    {
        return $this->auto_generation;
    }

    public function getAutoModification(): ?AutoModification
    {
        return $this->auto_modification;
    }

    public function isEqual(AutoModification $autoModification): bool
    {
        if ($this->auto_modification && $this->auto_modification->getId() == $autoModification->getId()) return true;
        if ($this->auto_generation && $this->auto_generation->getId() == $autoModification->getAutoGeneration()->getId()) return true;
        if ($this->auto_model && $this->auto_model->getId() == $autoModification->getAutoGeneration()->getModel()->getId()) return true;
        if ($this->auto_marka && $this->auto_marka->getId() == $autoModification->getAutoGeneration()->getModel()->getMarka()->getId()) return true;
        return false;
    }
}
