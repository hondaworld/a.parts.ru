<?php

namespace App\Model\Auto\Entity\Modification;

use App\Model\Auto\Entity\Generation\AutoGeneration;
use App\Model\Work\Entity\Group\WorkGroup;
use App\Model\Work\Entity\Link\LinkWorkAuto;
use App\Model\Work\Entity\Link\LinkWorkNormaAuto;
use App\Model\Work\Entity\Link\LinkWorkPartsAuto;
use App\Model\Work\Entity\Period\WorkPeriod;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AutoModificationRepository::class)
 * @ORM\Table(name="auto_modification")
 */
class AutoModification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="auto_modificationID")
     */
    private $auto_modificationID;

    /**
     * @var AutoGeneration
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\Generation\AutoGeneration", inversedBy="modifications")
     * @ORM\JoinColumn(name="auto_generationID", referencedColumnName="auto_generationID", nullable=false)
     */
    private $generation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var WorkPeriod[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Period\WorkPeriod", mappedBy="auto_modification")
     * @ORM\OrderBy({"number" = "ASC"})
     */
    private $work_periods;

    /**
     * @var LinkWorkAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Link\LinkWorkAuto", mappedBy="auto_modification", orphanRemoval=true, cascade={"persist"})
     */
    private $work_autos;

    /**
     * @var LinkWorkNormaAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Link\LinkWorkNormaAuto", mappedBy="auto_modification", orphanRemoval=true, cascade={"persist"})
     */
    private $work_autos_norma;

    /**
     * @var LinkWorkPartsAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Link\LinkWorkPartsAuto", mappedBy="auto_modification", orphanRemoval=true, cascade={"persist"})
     */
    private $work_autos_parts;

    public function __construct(AutoGeneration $generation, string $name)
    {
        $this->generation = $generation;
        $this->name = $name;
    }

    public function update(string $name)
    {
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->auto_modificationID;
    }

    public function getAutoGeneration(): AutoGeneration
    {
        return $this->generation;
    }

    public function getName(): ?string
    {
        return $this->name;
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

    public function getNameWithGeneration(): string
    {
        return $this->getAutoGeneration()->getName() . ' (' . $this->getAutoGeneration()->getYearfrom() . '-' . ($this->getAutoGeneration()->getYearto() == '' ? 'н.в.' : $this->getAutoGeneration()->getYearto()) . ') ' . $this->getName();
    }

    /**
     * @return WorkPeriod[]|ArrayCollection
     */
    public function getWorkPeriods()
    {
        return $this->work_periods->toArray();
    }

    /**
     * @return LinkWorkAuto[]|ArrayCollection
     */
    public function getWorkAutos()
    {
        return $this->work_autos;
    }

    /**
     * @return LinkWorkNormaAuto[]|ArrayCollection
     */
    public function getWorkAutosNorma()
    {
        return $this->work_autos_norma;
    }

    /**
     * @return LinkWorkPartsAuto[]|ArrayCollection
     */
    public function getWorkAutosParts()
    {
        return $this->work_autos_parts;
    }

    /**
     * @param LinkWorkAuto[] $linkWorkAuto
     * @param WorkGroup $workGroup
     * @return bool
     */
    public function getWorkAutosByWorkGroup(array $linkWorkAuto, WorkGroup $workGroup): bool
    {
        foreach ($linkWorkAuto as $item) {
            if ($item->getAutoModification() && $item->getAutoModification()->getId() == $this->getId() && $item->getWorkGroup()->getId() == $workGroup->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param LinkWorkNormaAuto[] $linkWorkNormaAuto
     * @param WorkGroup $workGroup
     * @return string|null
     */
    public function getWorkAutosNormaByWorkGroup(array $linkWorkNormaAuto, WorkGroup $workGroup): ?string
    {
        foreach ($linkWorkNormaAuto as $item) {
            if ($item->getAutoModification() && $item->getAutoModification()->getId() == $this->getId() && $item->getWorkGroup()->getId() == $workGroup->getId()) {
                return $item->getNorma();
            }
        }
        return null;
    }

    /**
     * @param LinkWorkPartsAuto[] $linkWorkPartsAuto
     * @param WorkGroup $workGroup
     * @return string|null
     */
    public function getWorkAutosPartsByWorkGroup(array $linkWorkPartsAuto, WorkGroup $workGroup): ?string
    {
        foreach ($linkWorkPartsAuto as $item) {
            if ($item->getAutoModification() && $item->getAutoModification()->getId() == $this->getId() && $item->getWorkGroup()->getId() == $workGroup->getId()) {
                return $item->getParts();
            }
        }
        return null;
    }
}
