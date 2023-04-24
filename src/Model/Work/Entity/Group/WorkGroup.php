<?php

namespace App\Model\Work\Entity\Group;

use App\Model\Work\Entity\Category\WorkCategory;
use App\Model\Work\Entity\Link\LinkWorkAuto;
use App\Model\Work\Entity\Link\LinkWorkNormaAuto;
use App\Model\Work\Entity\Link\LinkWorkPartsAuto;
use App\Model\Work\Entity\Period\WorkPeriod;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @ORM\Entity(repositoryClass=WorkGroupRepository::class)
 * @ORM\Table(name="workGroup")
 */
class WorkGroup
{
    public const TO = [
        0 => 'Нет',
        1 => 'ТО',
        2 => 'Диагностика'
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="workGroupID")
     */
    private $workGroupID;

    /**
     * @var WorkCategory
     * @ORM\ManyToOne(targetEntity="App\Model\Work\Entity\Category\WorkCategory", inversedBy="groups")
     * @ORM\JoinColumn(name="workCategoryID", referencedColumnName="workCategoryID")
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="koef", precision=4, scale=2)
     */
    private $norma;

    /**
     * @ORM\Column(type="koef", precision=4, scale=2)
     */
    private $norma_paint;

    /**
     * @ORM\Column(type="koef", precision=4, scale=2)
     */
    private $norma1;

    /**
     * @ORM\Column(type="koef", precision=4, scale=2)
     */
    private $norma2;

    /**
     * @ORM\Column(type="boolean", name="isBody")
     */
    private $isBody = false;

    /**
     * @ORM\Column(type="smallint", name="isTO")
     */
    private $isTO = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort;

    /**
     * @var WorkPeriod[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\Work\Entity\Period\WorkPeriod", mappedBy="groups")
     */
    private $periods;

    /**
     * @var WorkPeriod[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\Work\Entity\Period\WorkPeriod", mappedBy="groups_dop")
     */
    private $periods_dop;

    /**
     * @var WorkPeriod[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\Work\Entity\Period\WorkPeriod", mappedBy="groups_rec")
     */
    private $periods_rec;

    /**
     * @var LinkWorkAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Link\LinkWorkAuto", mappedBy="workGroup", orphanRemoval=true)
     */
    private $autos;

    /**
     * @var LinkWorkNormaAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Link\LinkWorkNormaAuto", mappedBy="workGroup", orphanRemoval=true)
     */
    private $autos_norma;

    /**
     * @var LinkWorkPartsAuto[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Work\Entity\Link\LinkWorkPartsAuto", mappedBy="workGroup", orphanRemoval=true)
     */
    private $autos_parts;

    public function __construct(WorkCategory $category, string $name, string $norma, int $isTO, ?int $sort)
    {
        $this->category = $category;
        $this->name = $name;
        $this->norma = $norma;
        $this->isTO = $isTO;
        $this->sort = $sort ?: 0;
    }

    public function update(WorkCategory $category, string $name, string $norma, int $isTO, ?int $sort)
    {
        $this->category = $category;
        $this->name = $name;
        $this->norma = $norma;
        $this->isTO = $isTO;
        $this->sort = $sort ?: 0;
    }

    public function getId(): int
    {
        return $this->workGroupID;
    }

    public function getCategory(): WorkCategory
    {
        return $this->category;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNorma(): string
    {
        return $this->norma;
    }

    public function getNormaPaint(): string
    {
        return $this->norma_paint;
    }

    public function getNorma1(): string
    {
        return $this->norma1;
    }

    public function getNorma2(): string
    {
        return $this->norma2;
    }

    public function isBody(): bool
    {
        return $this->isBody;
    }

    public function getIsTO(): int
    {
        return $this->isTO;
    }

    public function isTO(): bool
    {
        return $this->getIsTO() > 0;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @return LinkWorkAuto[]|ArrayCollection
     */
    public function getAutos()
    {
        return $this->autos->toArray();
    }

    /**
     * @return LinkWorkNormaAuto[]|ArrayCollection
     */
    public function getAutosNorma()
    {
        return $this->autos_norma->toArray();
    }

    /**
     * @return LinkWorkPartsAuto[]|ArrayCollection
     */
    public function getAutosParts()
    {
        return $this->autos_parts->toArray();
    }

}
