<?php

namespace App\Model\Expense\Entity\SkladDocument;

use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExpenseSkladDocumentRepository::class)
 * @ORM\Table(name="expense_skladDocuments")
 */
class ExpenseSkladDocument
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="expense_skladDocumentID")
     */
    private $expense_skladDocumentID;

    /**
     * @var DocumentType
     * @ORM\ManyToOne(targetEntity="App\Model\Document\Entity\Type\DocumentType", inversedBy="expenseSkladDocuments")
     * @ORM\JoinColumn(name="doc_typeID", referencedColumnName="doc_typeID", nullable=false)
     */
    private $document_type;

    /**
     * @var Document
     * @ORM\Embedded(class="Document", columnPrefix="document_")
     */
    private $document;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="expenseSkladDocuments")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=false)
     */
    private $manager;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad", inversedBy="expenseSkladDocuments")
     * @ORM\JoinColumn(name="zapSkladID", referencedColumnName="zapSkladID", nullable=false)
     */
    private $zapSklad;

    /**
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad", inversedBy="expenseSkladDocumentsTo")
     * @ORM\JoinColumn(name="zapSkladID_to", referencedColumnName="zapSkladID", nullable=false)
     */
    private $zapSklad_to;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="expenseSkladDocuments")
     * @ORM\JoinColumn(name="firmID", referencedColumnName="firmID", nullable=false)
     */
    private $firm;

    /**
     * @var ExpenseSklad[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Sklad\ExpenseSklad", mappedBy="expense_skladDocument")
     */
    private $expense_sklads;

    /**
     * @param DocumentType $document_type
     * @param Document $document
     * @param Manager $manager
     * @param ZapSklad $zapSklad
     * @param ZapSklad $zapSklad_to
     * @param Firm $firm
     */
    public function __construct(DocumentType $document_type, Document $document, Manager $manager, ZapSklad $zapSklad, ZapSklad $zapSklad_to, Firm $firm)
    {
        $this->document_type = $document_type;
        $this->document = $document;
        $this->manager = $manager;
        $this->dateofadded = new \DateTime();
        $this->zapSklad = $zapSklad;
        $this->zapSklad_to = $zapSklad_to;
        $this->firm = $firm;
    }


    public function getId(): ?int
    {
        return $this->expense_skladDocumentID;
    }

    public function getDocType(): DocumentType
    {
        return $this->document_type;
    }

    public function getManager(): Manager
    {
        return $this->manager;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getZapSklad(): ZapSklad
    {
        return $this->zapSklad;
    }

    public function getZapSkladTo(): ZapSklad
    {
        return $this->zapSklad_to;
    }

    public function getFirm(): Firm
    {
        return $this->firm;
    }

    /**
     * @return Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * @return ExpenseSklad[]|ArrayCollection
     */
    public function getExpenseSklads()
    {
        return $this->expense_sklads->toArray();
    }

}
