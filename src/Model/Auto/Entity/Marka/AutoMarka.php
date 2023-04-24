<?php

namespace App\Model\Auto\Entity\Marka;

use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Auto\Entity\MotoModel\MotoModel;
use App\Model\Order\Entity\Site\Site;
use App\Model\Work\Entity\Group\WorkGroup;
use App\Model\Work\Entity\Link\LinkWorkAuto;
use App\Model\Work\Entity\Link\LinkWorkNormaAuto;
use App\Model\Work\Entity\Link\LinkWorkPartsAuto;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @ORM\Entity(repositoryClass=AutoMarkaRepository::class)
 * @ORM\Table(name="auto_marka")
 */
class AutoMarka
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="auto_markaID")
     */
    private $auto_markaID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name_rus;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var AutoModel[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Auto\Entity\Model\AutoModel", mappedBy="marka")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $models;

    /**
     * @var MotoModel[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Auto\Entity\MotoModel\MotoModel", mappedBy="marka")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $moto_models;

    /**
     * @var Site[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\Order\Entity\Site\Site", mappedBy="auto_marka")
     */
    private $sites;

    /**
     * @var LinkWorkAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Link\LinkWorkAuto", mappedBy="auto_marka", orphanRemoval=true, cascade={"persist"})
     */
    private $work_autos;

    /**
     * @var LinkWorkNormaAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Link\LinkWorkNormaAuto", mappedBy="auto_marka", orphanRemoval=true, cascade={"persist"})
     */
    private $work_autos_norma;

    /**
     * @var LinkWorkPartsAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Link\LinkWorkPartsAuto", mappedBy="auto_marka", orphanRemoval=true, cascade={"persist"})
     */
    private $work_autos_parts;

    public function __construct(string $name, string $name_rus)
    {
        $this->name = $name;
        $this->name_rus = $name_rus;
    }

    public function update(string $name, string $name_rus)
    {
        $this->name = $name;
        $this->name_rus = $name_rus;
    }

    public function getId(): ?int
    {
        return $this->auto_markaID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNameRus(): ?string
    {
        return $this->name_rus;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    /**
     * @return AutoModel[]|ArrayCollection
     */
    public function getModels()
    {
        return $this->models->toArray();
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
     * @return MotoModel[]|ArrayCollection
     */
    public function getMotoModels()
    {
        return $this->moto_models->toArray();
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
            if ($item->getAutoMarka() && $item->getAutoMarka()->getId() == $this->getId() && $item->getWorkGroup()->getId() == $workGroup->getId()) {
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
            if ($item->getAutoMarka() && $item->getAutoMarka()->getId() == $this->getId() && $item->getWorkGroup()->getId() == $workGroup->getId()) {
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
            if ($item->getAutoMarka() && $item->getAutoMarka()->getId() == $this->getId() && $item->getWorkGroup()->getId() == $workGroup->getId()) {
                return $item->getParts();
            }
        }
        return null;
    }

}
