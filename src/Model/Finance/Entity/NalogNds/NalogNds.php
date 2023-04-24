<?php

namespace App\Model\Finance\Entity\NalogNds;

use App\Model\Finance\Entity\Nalog\Nalog;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @ORM\Entity(repositoryClass=NalogNdsRepository::class)
 * @ORM\Table(name="nalogNds")
 */
class NalogNds
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="nalogNdsID")
     */
    private $nalogNdsID;

    /**
     * @var Nalog
     * @ORM\ManyToOne(targetEntity="App\Model\Finance\Entity\Nalog\Nalog", inversedBy="nds")
     * @ORM\JoinColumn(name="nalogID", referencedColumnName="nalogID", nullable=false)
     */
    private $nalog;

    /**
     * @ORM\Column(type="date")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=2)
     */
    private $nds;

    public function __construct(Nalog $nalog, \DateTime $dateofadded, string $nds)
    {
        $this->nalog = $nalog;
        $this->dateofadded = $dateofadded;
        $this->nds = str_replace(',', '.', $nds);
    }

    public function update(\DateTime $dateofadded, string $nds)
    {
        $this->dateofadded = $dateofadded;
        $this->nds = str_replace(',', '.', $nds);
    }

    public function getId(): ?int
    {
        return $this->nalogNdsID;
    }

    public function getNalog(): ?Nalog
    {
        return $this->nalog;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getNds(): ?string
    {
        return $this->nds;
    }
}
