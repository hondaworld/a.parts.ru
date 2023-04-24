<?php

namespace App\Model\Expense\Entity\SchetFakKor;

use App\Model\Expense\Entity\SchetFak\SchetFak;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Income\Entity\Document\IncomeDocument;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SchetFakKorRepository::class)
 * @ORM\Table(name="schet_fak_kor")
 */
class SchetFakKor
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="schet_fak_korID")
     */
    private $schet_fak_korID;

    /**
     * @var SchetFak
     * @ORM\ManyToOne(targetEntity="App\Model\Expense\Entity\SchetFak\SchetFak", inversedBy="schet_fak_kors")
     * @ORM\JoinColumn(name="schet_fakID", referencedColumnName="schet_fakID", nullable=true)
     */
    private $schet_fak;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="schet_fak_kors")
     * @ORM\JoinColumn(name="firmID", referencedColumnName="firmID", nullable=true)
     */
    private $firm;

    /**
     * @var Document
     * @ORM\Embedded(class="Document", columnPrefix="document_")
     */
    private $document;

    /**
     * @ORM\Column(type="date")
     */
    private $dateofadded;

    /**
     * @var SchetFak[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Expense\Entity\SchetFak\SchetFak", inversedBy="schet_fak_kors_addition")
     * @ORM\JoinTable(name="link_SFK_SF",
     *      joinColumns={@ORM\JoinColumn(name="schet_fak_korID", referencedColumnName="schet_fak_korID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="schet_fakID", referencedColumnName="schet_fakID")}
     * )
     */
    private $schet_faks;

    /**
     * @var IncomeDocument[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Income\Entity\Document\IncomeDocument", inversedBy="schet_fak_kors")
     * @ORM\JoinTable(name="link_SFK_VZ",
     *      joinColumns={@ORM\JoinColumn(name="schet_fak_korID", referencedColumnName="schet_fak_korID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="incomeDocumentID", referencedColumnName="incomeDocumentID")}
     * )
     */
    private $incomeDocuments;

    public function __construct(Document $document, Firm $firm, SchetFak $schet_fak)
    {
        $this->document = $document;
        $this->firm = $firm;
        $this->schet_fak = $schet_fak;
        $this->dateofadded = new \DateTime();
        $this->schet_faks = new ArrayCollection();
        $this->assignSchetFak($schet_fak);
        $this->incomeDocuments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->schet_fak_korID;
    }

    public function getSchetFak(): SchetFak
    {
        return $this->schet_fak;
    }

    public function getFirm(): Firm
    {
        return $this->firm;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function getDateofadded(): ?\DateTime
    {
        return $this->dateofadded;
    }

    /**
     * @return SchetFak[]|ArrayCollection
     */
    public function getSchetFaks()
    {
        return $this->schet_faks;
    }

    /**
     * @param SchetFak $schetFak
     */
    public function assignSchetFak(SchetFak $schetFak): void
    {
        $this->schet_faks->add($schetFak);
    }

    /**
     * @param SchetFak $schetFak
     */
    public function removeSchetFak(SchetFak $schetFak): void
    {
        $this->schet_faks->removeElement($schetFak);
    }

    public function clearSchetFaks(): void
    {
        $this->schet_faks->clear();
    }

    /**
     * @return IncomeDocument[]|ArrayCollection
     */
    public function getIncomeDocuments()
    {
        return $this->incomeDocuments;
    }

    /**
     * @param IncomeDocument $incomeDocument
     */
    public function assignIncomeDocument(IncomeDocument $incomeDocument): void
    {
        $this->incomeDocuments->add($incomeDocument);
    }

    /**
     * @param IncomeDocument $incomeDocument
     */
    public function removeIncomeDocument(IncomeDocument $incomeDocument): void
    {
        $this->incomeDocuments->removeElement($incomeDocument);
    }

    public function clearIncomeDocuments(): void
    {
        $this->incomeDocuments->clear();
    }

}
