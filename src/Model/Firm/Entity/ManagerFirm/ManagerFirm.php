<?php

namespace App\Model\Firm\Entity\ManagerFirm;

use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\Entity\OrgGroup\OrgGroup;
use App\Model\Firm\Entity\OrgJob\OrgJob;
use App\Model\Manager\Entity\Manager\Manager;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ManagerFirmRepository::class)
 * @ORM\Table(name="linkManagerFirm")
 */
class ManagerFirm
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="linkID")
     */
    private $id;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="manager_firms", fetch="EAGER")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=false)
     */
    private $manager;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="manager_firms", fetch="EAGER")
     * @ORM\JoinColumn(name="firmID", referencedColumnName="firmID", nullable=false)
     */
    private $firm;

    /**
     * @var OrgGroup
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\OrgGroup\OrgGroup", inversedBy="manager_firms", fetch="EAGER")
     * @ORM\JoinColumn(name="org_groupID", referencedColumnName="org_groupID", nullable=false)
     */
    private $org_group;

    /**
     * @var OrgJob
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\OrgJob\OrgJob", inversedBy="manager_firms", fetch="EAGER")
     * @ORM\JoinColumn(name="org_jobID", referencedColumnName="org_jobID", nullable=false)
     */
    private $org_job;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateofadded;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateofclosed;

    public function __construct(Manager $manager, Firm $firm, OrgGroup $org_group, OrgJob $org_job, ?\DateTime $dateofadded, ?\DateTime $dateofclosed)
    {
        $this->manager = $manager;
        $this->firm = $firm;
        $this->org_group = $org_group;
        $this->org_job = $org_job;
        $this->dateofadded = $dateofadded;
        $this->dateofclosed = $dateofclosed;
    }

    public function update(OrgGroup $org_group, OrgJob $org_job, ?\DateTime $dateofadded, ?\DateTime $dateofclosed)
    {
        $this->org_group = $org_group;
        $this->org_job = $org_job;
        $this->dateofadded = $dateofadded;
        $this->dateofclosed = $dateofclosed;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        if ($this->dateofadded && $this->dateofadded->format('Y') == '-0001') return null;
        return $this->dateofadded;
    }

    public function getDateofclosed(): ?\DateTimeInterface
    {
        if ($this->dateofclosed && $this->dateofclosed->format('Y') == '-0001') return null;
        return $this->dateofclosed;
    }

    /**
     * @return Manager
     */
    public function getManager(): Manager
    {
        return $this->manager;
    }

    /**
     * @return Firm
     */
    public function getFirm(): Firm
    {
        return $this->firm;
    }

    /**
     * @return OrgGroup
     */
    public function getOrgGroup(): OrgGroup
    {
        return $this->org_group;
    }

    /**
     * @return OrgJob
     */
    public function getOrgJob(): OrgJob
    {
        return $this->org_job;
    }

}
