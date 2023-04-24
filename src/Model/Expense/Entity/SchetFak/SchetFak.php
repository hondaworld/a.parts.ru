<?php

namespace App\Model\Expense\Entity\SchetFak;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\SchetFakKor\SchetFakKor;
use App\Model\Expense\Entity\SchetFakPrint\SchetFakPrint;
use App\Model\Finance\Entity\Nalog\Nalog;
use App\Model\Firm\Entity\Firm\Firm;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @ORM\Entity(repositoryClass=SchetFakRepository::class)
 * @ORM\Table(name="schet_fak")
 */
class SchetFak
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="schet_fakID")
     */
    private $schet_fakID;

    /**
     * @var ExpenseDocument
     * @ORM\OneToOne(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", inversedBy="schet_fak")
     * @ORM\JoinColumn(name="expenseDocumentID", referencedColumnName="expenseDocumentID", nullable=false)
     */
    private $expenseDocument;

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
     * @var Nalog
     * @ORM\ManyToOne(targetEntity="App\Model\Finance\Entity\Nalog\Nalog", inversedBy="schet_faks")
     * @ORM\JoinColumn(name="nalogID", referencedColumnName="nalogID", nullable=false)
     */
    private $nalog;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="schet_faks")
     * @ORM\JoinColumn(name="firmID", referencedColumnName="firmID", nullable=false)
     */
    private $firm;

    /**
     * @var SchetFakPrint
     * @ORM\OneToOne(targetEntity="App\Model\Expense\Entity\SchetFakPrint\SchetFakPrint", mappedBy="schet_fak")
     */
    private $schet_fak_print;

    /**
     * @var SchetFakKor[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\SchetFakKor\SchetFakKor", mappedBy="schet_fak")
     */
    private $schet_fak_kors;

    /**
     * @var SchetFakKor[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\Expense\Entity\SchetFakKor\SchetFakKor", mappedBy="schet_faks")
     */
    private $schet_fak_kors_addition;

    public function __construct(ExpenseDocument $expenseDocument, Document $document, Nalog $nalog, Firm $firm)
    {
        $this->expenseDocument = $expenseDocument;
        $this->document = $document;
        $this->nalog = $nalog;
        $this->firm = $firm;
        $this->dateofadded = new \DateTime();
    }

    public function getId(): int
    {
        return $this->schet_fakID;
    }

    public function getExpenseDocument(): ExpenseDocument
    {
        return $this->expenseDocument;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function getDateofadded(): ?\DateTime
    {
        return $this->dateofadded;
    }

    public function getNalog(): Nalog
    {
        return $this->nalog;
    }

    public function getFirm(): Firm
    {
        return $this->firm;
    }

    /**
     * @return SchetFakPrint|null
     */
    public function getSchetFakPrint(): ?SchetFakPrint
    {
        return $this->schet_fak_print;
    }

    /**
     * @return SchetFakKor[]|ArrayCollection
     */
    public function getSchetFakKors()
    {
        return $this->schet_fak_kors;
    }
}
