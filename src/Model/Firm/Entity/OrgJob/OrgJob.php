<?php

namespace App\Model\Firm\Entity\OrgJob;

use App\Model\Firm\Entity\ManagerFirm\ManagerFirm;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrgJobRepository::class)
 * @ORM\Table(name="org_jobs")
 */
class OrgJob
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="org_jobID")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var ManagerFirm[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\ManagerFirm\ManagerFirm", mappedBy="org_job")
     */
    private $manager_firms;

    public function __construct(string $name, bool $isMain)
    {
        $this->name = $name;
        $this->isMain = $isMain;
    }

    public function update(string $name, bool $isMain)
    {
        $this->name = $name;
        $this->isMain = $isMain;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    public function hide()
    {
        $this->isHide = true;
    }

    public function unhide()
    {
        $this->isHide = false;
    }

    public function getManagerFirms(): array
    {
        return $this->manager_firms->toArray();
    }
}
