<?php

namespace App\Model\Finance\Entity\Nalog;

use App\Model\Expense\Entity\SchetFak\SchetFak;
use App\Model\Finance\Entity\NalogNds\NalogNds;
use App\Model\Firm\Entity\Firm\Firm;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @ORM\Entity(repositoryClass=NalogRepository::class)
 * @ORM\Table(name="nalogs")
 */
class Nalog
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="nalogID")
     */
    private $nalogID;

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
     * @var Firm[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\Firm\Firm", mappedBy="nalog")
     */
    private $firms;

    /**
     * @var SchetFak[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\SchetFak\SchetFak", mappedBy="nalog")
     */
    private $schet_faks;

    /**
     * @var NalogNds[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Finance\Entity\NalogNds\NalogNds", mappedBy="nalog", orphanRemoval=true, cascade={"persist"})
     * @ORM\OrderBy({"dateofadded" = "DESC"})
     */
    private $nds;

    public function __construct(string $name)
    {
        $this->name = $name;
        $this->nds = new ArrayCollection();
    }

    public function update(string $name)
    {
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->nalogID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getIsMain(): ?bool
    {
        return $this->isMain;
    }

    public function getIsHide(): ?bool
    {
        return $this->isHide;
    }

    public function getNds(): array
    {
        return $this->nds->toArray();
    }

    public function getLastNds(): ?NalogNds
    {
        if ($this->nds->count() > 0) return $this->nds[0];
        return null;
    }

    public function addNds(\DateTime $dateofadded, string $nds)
    {
        $this->nds->add(new NalogNds($this, $dateofadded, $nds));
    }
}
