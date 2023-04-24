<?php

namespace App\Model\Auto\Entity\Generation;

use App\Model\Auto\Entity\Engine\AutoEngine;
use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Auto\Entity\Modification\AutoModification;
use App\Model\Work\Entity\Group\WorkGroup;
use App\Model\Work\Entity\Link\LinkWorkAuto;
use App\Model\Work\Entity\Link\LinkWorkNormaAuto;
use App\Model\Work\Entity\Link\LinkWorkPartsAuto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AutoGenerationRepository::class)
 * @ORM\Table(name="auto_generation")
 */
class AutoGeneration
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="auto_generationID")
     */
    private $auto_generationID;

    /**
     * @var AutoModel
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\Model\AutoModel", inversedBy="generations")
     * @ORM\JoinColumn(name="auto_modelID", referencedColumnName="auto_modelID", nullable=false)
     */
    private $model;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name_rus;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $yearfrom;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $yearto;

    /**
     * @var Description
     * @ORM\Embedded(class="Description", columnPrefix="description_")
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $photo = '';

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var AutoModification[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Auto\Entity\Modification\AutoModification", mappedBy="generation")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $modifications;

    /**
     * @var AutoEngine[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Auto\Entity\Engine\AutoEngine", mappedBy="generation")
     */
    private $engines;

    /**
     * @var LinkWorkAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Link\LinkWorkAuto", mappedBy="auto_generation", orphanRemoval=true, cascade={"persist"})
     */
    private $work_autos;

    /**
     * @var LinkWorkNormaAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Link\LinkWorkNormaAuto", mappedBy="auto_generation", orphanRemoval=true, cascade={"persist"})
     */
    private $work_autos_norma;

    /**
     * @var LinkWorkPartsAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Link\LinkWorkPartsAuto", mappedBy="auto_generation", orphanRemoval=true, cascade={"persist"})
     */
    private $work_autos_parts;

    public function __construct(AutoModel $model, string $name, string $name_rus, string $yearfrom, ?string $yearto)
    {
        $this->model = $model;
        $this->name = $name;
        $this->name_rus = $name_rus;
        $this->yearfrom = $yearfrom;
        $this->yearto = $yearto ?: '';
        $this->description = new Description();
    }

    public function update(string $name, string $name_rus, string $yearfrom, ?string $yearto)
    {
        $this->name = $name;
        $this->name_rus = $name_rus;
        $this->yearfrom = $yearfrom;
        $this->yearto = $yearto ?: '';
    }

    public function updatePhoto(?string $photo)
    {
        $this->photo = $photo ?: '';
    }

    public function getId(): ?int
    {
        return $this->auto_generationID;
    }

    public function getModel(): AutoModel
    {
        return $this->model;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNameRus(): ?string
    {
        return $this->name_rus;
    }

    public function getYearfrom(): ?string
    {
        return $this->yearfrom;
    }

    public function getYearto(): ?string
    {
        return $this->yearto;
    }

    public function getDescription(): Description
    {
        return $this->description;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function removePhoto(): void
    {
        $this->photo = '';
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
     * @return AutoModification[]|ArrayCollection
     */
    public function getModifications()
    {
        return $this->modifications->toArray();
    }

    /**
     * @return AutoEngine[]|ArrayCollection
     */
    public function getEngines()
    {
        return $this->engines->toArray();
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
            if ($item->getAutoGeneration() && $item->getAutoGeneration()->getId() == $this->getId() && $item->getWorkGroup()->getId() == $workGroup->getId()) {
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
            if ($item->getAutoGeneration() && $item->getAutoGeneration()->getId() == $this->getId() && $item->getWorkGroup()->getId() == $workGroup->getId()) {
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
            if ($item->getAutoGeneration() && $item->getAutoGeneration()->getId() == $this->getId() && $item->getWorkGroup()->getId() == $workGroup->getId()) {
                return $item->getParts();
            }
        }
        return null;
    }

}
