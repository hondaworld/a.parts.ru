<?php

namespace App\Model\Work\Entity\Period;

use App\Model\Auto\Entity\Modification\AutoModification;
use App\Model\Work\Entity\Group\WorkGroup;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=WorkPeriodRepository::class)
 * @ORM\Table(name="workPeriod")
 */
class WorkPeriod
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="workPeriodID")
     */
    private $workPeriodID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var AutoModification
     * @ORM\ManyToOne(targetEntity="App\Model\Auto\Entity\Modification\AutoModification", inversedBy="work_periods")
     * @ORM\JoinColumn(name="auto_modificationID", referencedColumnName="auto_modificationID", nullable=false)
     */
    private $auto_modification;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @ORM\Column(type="koef", precision=5, scale=2)
     */
    private $norma;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var WorkGroup[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Work\Entity\Group\WorkGroup", inversedBy="periods")
     * @ORM\JoinTable(name="linkWorkPeriodGroup",
     *      joinColumns={@ORM\JoinColumn(name="workPeriodID", referencedColumnName="workPeriodID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="workGroupID", referencedColumnName="workGroupID")}
     * )
     * @ORM\OrderBy({"sort" = "ASC"})
     */
    private $groups;

    /**
     * @var WorkGroup[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Work\Entity\Group\WorkGroup", inversedBy="periods_dop")
     * @ORM\JoinTable(name="linkWorkPeriodGroupDop",
     *      joinColumns={@ORM\JoinColumn(name="workPeriodID", referencedColumnName="workPeriodID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="workGroupID", referencedColumnName="workGroupID")}
     * )
     * @ORM\OrderBy({"sort" = "ASC"})
     */
    private $groups_dop;

    /**
     * @var WorkGroup[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Work\Entity\Group\WorkGroup", inversedBy="periods_rec")
     * @ORM\JoinTable(name="linkWorkPeriodGroupRec",
     *      joinColumns={@ORM\JoinColumn(name="workPeriodID", referencedColumnName="workPeriodID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="workGroupID", referencedColumnName="workGroupID")}
     * )
     * @ORM\OrderBy({"sort" = "ASC"})
     */
    private $groups_rec;

    public function __construct(AutoModification $auto_modification, string $name, string $norma, int $number)
    {
        $this->name = $name;
        $this->auto_modification = $auto_modification;
        $this->norma = $norma;
        $this->number = $number;
        $this->groups = new ArrayCollection();
        $this->groups_dop = new ArrayCollection();
        $this->groups_rec = new ArrayCollection();
    }

    public function update(string $name, string $norma)
    {
        $this->name = $name;
        $this->norma = $norma;
    }

    public function getId(): ?int
    {
        return $this->workPeriodID;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAutoModification(): AutoModification
    {
        return $this->auto_modification;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function changeNumber(int $sort): void
    {
        $this->number = $sort;
    }

    public function getNorma(): string
    {
        return $this->norma;
    }

    public function isHide(): bool
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
     * @return WorkGroup[]
     */
    public function getGroups()
    {
        return array_filter($this->groups->toArray(), function(WorkGroup $workGroup) {
            return $workGroup->isTO();
        });
    }

    /**
     * @return WorkGroup[]
     */
    public function getGroupsDop()
    {
        return array_filter($this->groups_dop->toArray(), function(WorkGroup $workGroup) {
            return $workGroup->isTO();
        });
    }

    /**
     * @return WorkGroup[]
     */
    public function getGroupsRec()
    {
        return array_filter($this->groups_rec->toArray(), function(WorkGroup $workGroup) {
            return $workGroup->isTO();
        });
    }

    public function clearGroups(): void
    {
        $this->groups->clear();
    }

    /**
     * @param WorkGroup $workGroup
     */
    public function assignGroup(WorkGroup $workGroup): void
    {
        $this->groups->add($workGroup);
    }

    public function clearGroupsDop(): void
    {
        $this->groups_dop->clear();
    }

    /**
     * @param WorkGroup $workGroup
     */
    public function assignGroupDop(WorkGroup $workGroup): void
    {
        $this->groups_dop->add($workGroup);
    }

    public function clearGroupsRec(): void
    {
        $this->groups_rec->clear();
    }

    /**
     * @param WorkGroup $workGroup
     */
    public function assignGroupRec(WorkGroup $workGroup): void
    {
        $this->groups_rec->add($workGroup);
    }

}
