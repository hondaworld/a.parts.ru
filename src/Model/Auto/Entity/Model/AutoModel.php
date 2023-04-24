<?php

namespace App\Model\Auto\Entity\Model;

use App\Model\Auto\Entity\Auto\Auto;
use App\Model\Auto\Entity\Generation\AutoGeneration;
use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Card\Entity\Auto\ZapCardAuto;
use App\Model\Detail\Entity\Kit\ZapCardKit;
use App\Model\Work\Entity\Group\WorkGroup;
use App\Model\Work\Entity\Link\LinkWorkAuto;
use App\Model\Work\Entity\Link\LinkWorkNormaAuto;
use App\Model\Work\Entity\Link\LinkWorkPartsAuto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AutoModelRepository::class)
 * @ORM\Table(name="auto_model")
 */
class AutoModel
{
    public const PHOTO_MAX_WIDTH = 350;
    public const PHOTO_MAX_HEIGHT = 120;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="auto_modelID")
     */
    private $auto_modelID;

    /**
     * @var AutoMarka
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\Marka\AutoMarka", inversedBy="models")
     * @ORM\JoinColumn(name="auto_markaID", referencedColumnName="auto_markaID", nullable=false)
     */
    private $marka;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name_rus;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

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
     * @ORM\Column(type="string", length=15, name="cataloggroupName")
     */
    private $cataloggroupName = '';

    /**
     * @ORM\Column(type="integer", name="cataloggroupID")
     */
    private $cataloggroupID = 0;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var AutoGeneration[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Auto\Entity\Generation\AutoGeneration", mappedBy="model")
     * @ORM\OrderBy({"yearfrom" = "ASC"})
     */
    private $generations;

    /**
     * @var Auto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Auto\Entity\Auto\Auto", mappedBy="model")
     */
    private $autos;

    /**
     * @var ZapCardAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Auto\ZapCardAuto", mappedBy="auto_model")
     */
    private $zapCard_auto;

    /**
     * @var ZapCardKit[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Detail\Entity\Kit\ZapCardKit", mappedBy="auto_model")
     * @ORM\OrderBy({"sort" = "ASC"})
     */
    private $kits;

    /**
     * @var LinkWorkAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Link\LinkWorkAuto", mappedBy="auto_model", orphanRemoval=true, cascade={"persist"})
     */
    private $work_autos;

    /**
     * @var LinkWorkNormaAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Link\LinkWorkNormaAuto", mappedBy="auto_model", orphanRemoval=true, cascade={"persist"})
     */
    private $work_autos_norma;

    /**
     * @var LinkWorkPartsAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Link\LinkWorkPartsAuto", mappedBy="auto_model", orphanRemoval=true, cascade={"persist"})
     */
    private $work_autos_parts;

    public function __construct(AutoMarka $marka, string $name, string $name_rus, ?string $path)
    {
        $this->marka = $marka;
        $this->name = $name;
        $this->name_rus = $name_rus;
        $this->path = $path ?: '';
        $this->description = new Description();
    }

    public function update(string $name, string $name_rus, ?string $path)
    {
        $this->name = $name;
        $this->name_rus = $name_rus;
        $this->path = $path ?: '';
    }

    public function updatePhoto(?string $photo)
    {
        $this->photo = $photo ?: '';
    }

    public function getId(): ?int
    {
        return $this->auto_modelID;
    }

    public function getMarka(): AutoMarka
    {
        return $this->marka;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNameRus(): ?string
    {
        return $this->name_rus;
    }

    public function getPath(): ?string
    {
        return $this->path;
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

    public function getCataloggroupName(): ?string
    {
        return $this->cataloggroupName;
    }

    public function getCataloggroupID(): ?int
    {
        return $this->cataloggroupID;
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
     * @return AutoGeneration[]|ArrayCollection
     */
    public function getGenerations()
    {
        return $this->generations->toArray();
    }

    /**
     * @return Auto[]|ArrayCollection
     */
    public function getAutos()
    {
        return $this->autos->toArray();
    }

    /**
     * @return ZapCardKit[]|ArrayCollection
     */
    public function getKits()
    {
        return $this->kits->toArray();
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
            if ($item->getAutoModel() && $item->getAutoModel()->getId() == $this->getId() && $item->getWorkGroup()->getId() == $workGroup->getId()) {
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
            if ($item->getAutoModel() && $item->getAutoModel()->getId() == $this->getId() && $item->getWorkGroup()->getId() == $workGroup->getId()) {
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
            if ($item->getAutoModel() && $item->getAutoModel()->getId() == $this->getId() && $item->getWorkGroup()->getId() == $workGroup->getId()) {
                return $item->getParts();
            }
        }
        return null;
    }

}
