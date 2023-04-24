<?php

namespace App\Model\Expense\Entity\DocumentPrint;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExpenseDocumentPrintRepository::class)
 * @ORM\Table(name="expenseDocuments_print")
 */
class ExpenseDocumentPrint
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="expenseDocument_printID")
     */
    private $expenseDocument_printID;

    /**
     * @var ExpenseDocument
     * @ORM\OneToOne(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", inversedBy="expenseDocumentPrint")
     * @ORM\JoinColumn(name="expenseDocumentID", referencedColumnName="expenseDocumentID", nullable=false)
     */
    private $expenseDocument;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $director;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $buhgalter;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nakladnayaosn;

    /**
     * @var From
     * @ORM\Embedded(class="From", columnPrefix="from_")
     */
    private $from;

    /**
     * @var FromGruz
     * @ORM\Embedded(class="FromGruz", columnPrefix="from_")
     */
    private $from_gruz;

    /**
     * @var To
     * @ORM\Embedded(class="To", columnPrefix="to_")
     */
    private $to;

    /**
     * @var ToGruz
     * @ORM\Embedded(class="ToGruz", columnPrefix="to_")
     */
    private $to_gruz;

    /**
     * @var ToCash
     * @ORM\Embedded(class="ToCash", columnPrefix="to_")
     */
    private $to_cash;

    public function __construct(ExpenseDocument $expenseDocument, string $director, string $buhgalter, string $nakladnayaosn, From $from, FromGruz $from_gruz, To $to, ToGruz $to_gruz, ToCash $to_cash)
    {
        $this->expenseDocument = $expenseDocument;
        $this->director = $director;
        $this->buhgalter = $buhgalter;
        $this->nakladnayaosn = $nakladnayaosn;
        $this->from = $from;
        $this->from_gruz = $from_gruz;
        $this->to = $to;
        $this->to_gruz = $to_gruz;
        $this->to_cash = $to_cash;
    }

    public function getId(): ?int
    {
        return $this->expenseDocument_printID;
    }

    /**
     * @return ExpenseDocument
     */
    public function getExpenseDocument(): ExpenseDocument
    {
        return $this->expenseDocument;
    }

    /**
     * @return string
     */
    public function getDirector(): string
    {
        return $this->director;
    }

    /**
     * @return string
     */
    public function getBuhgalter(): string
    {
        return $this->buhgalter;
    }

    /**
     * @return string
     */
    public function getNakladnayaosn(): string
    {
        return $this->nakladnayaosn;
    }

    /**
     * @return From
     */
    public function getFrom(): From
    {
        return $this->from;
    }

    /**
     * @return FromGruz
     */
    public function getFromGruz(): FromGruz
    {
        return $this->from_gruz;
    }

    /**
     * @return To
     */
    public function getTo(): To
    {
        return $this->to;
    }

    /**
     * @return ToGruz
     */
    public function getToGruz(): ToGruz
    {
        return $this->to_gruz;
    }

    /**
     * @return ToCash
     */
    public function getToCash(): ToCash
    {
        return $this->to_cash;
    }

}
