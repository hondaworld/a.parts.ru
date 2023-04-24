<?php

namespace App\Model\Document\Entity\Type;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\SkladDocument\ExpenseSkladDocument;
use App\Model\Income\Entity\Document\IncomeDocument;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DocumentTypeRepository::class)
 * @ORM\Table(name="doc_types")
 */
class DocumentType
{
    public const S = 1;
    public const SF = 2;
    public const RN = 3;
    public const PN = 4;
    public const TCH = 5;
    public const VZ = 6;
    public const VN = 7;
    public const WON = 8;
    public const NP = 9;
    public const SFK = 10;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="doc_typeID")
     */
    private $doc_typeID;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $name_short;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $path_excel = '';

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $path_xml = '';

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var IncomeDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Document\IncomeDocument", mappedBy="document_type")
     */
    private $incomeDocuments;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="document_type")
     */
    private $expenseDocuments;

    /**
     * @var ExpenseSkladDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\SkladDocument\ExpenseSkladDocument", mappedBy="document_type")
     */
    private $expenseSkladDocuments;

    public function __construct(string $name_short, string $name, ?string $path)
    {
        $this->name_short = $name_short;
        $this->name = $name;
        $this->path = $path ?: '';
    }

    public function update(string $name_short, string $name, ?string $path)
    {
        $this->name_short = $name_short;
        $this->name = $name;
        $this->path = $path ?: '';
    }

    public function getId(): ?int
    {
        return $this->doc_typeID;
    }

    public function getNameShort(): ?string
    {
        return $this->name_short;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getPathExcel(): ?string
    {
        return $this->path_excel;
    }

    public function getPathXml(): ?string
    {
        return $this->path_xml;
    }

    public function isHide(): ?bool
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
     * @return IncomeDocument[]|ArrayCollection
     */
    public function getIncomeDocuments()
    {
        return $this->incomeDocuments->toArray();
    }

    /**
     * @return ExpenseDocument[]|ArrayCollection
     */
    public function getExpenseDocuments()
    {
        return $this->expenseDocuments->toArray();
    }

    /**
     * @return ExpenseSkladDocument[]|ArrayCollection
     */
    public function getExpenseSkladDocuments()
    {
        return $this->expenseSkladDocuments->toArray();
    }


}
