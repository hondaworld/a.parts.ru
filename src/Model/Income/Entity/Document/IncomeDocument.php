<?php

namespace App\Model\Income\Entity\Document;

use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Expense\Entity\SchetFakKor\SchetFakKor;
use App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Income\Entity\Good\IncomeGood;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Sklad\IncomeSklad;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @ORM\Entity(repositoryClass=IncomeDocumentRepository::class)
 * @ORM\Table(name="incomeDocuments")
 */
class IncomeDocument
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="incomeDocumentID")
     */
    private $incomeDocumentID;

    /**
     * @var DocumentType
     * @ORM\ManyToOne(targetEntity="App\Model\Document\Entity\Type\DocumentType", inversedBy="incomeDocuments", fetch="EAGER")
     * @ORM\JoinColumn(name="doc_typeID", referencedColumnName="doc_typeID", nullable=false)
     */
    private $document_type;

    /**
     * @var Document
     * @ORM\Embedded(class="Document", columnPrefix="document_")
     */
    private $document;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="incomeDocuments")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=true)
     */
    private $manager;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = 0;

    /**
     * @var Provider
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\Provider\Provider", inversedBy="incomeDocuments")
     * @ORM\JoinColumn(name="providerID", referencedColumnName="providerID", nullable=true)
     */
    private $provider;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="incomeDocuments")
     * @ORM\JoinColumn(name="userID", referencedColumnName="userID", nullable=true)
     */
    private $user;

    /**
     * @var Contact
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Contact\Contact", inversedBy="userIncomeDocuments")
     * @ORM\JoinColumn(name="user_contactID", referencedColumnName="contactID", nullable=true)
     */
    private $user_contact;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="firmFromIncomeDocuments")
     * @ORM\JoinColumn(name="firmID_from", referencedColumnName="firmID", nullable=true)
     */
    private $firm_from;

    /**
     * @var Contact
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Contact\Contact", inversedBy="firmFromIncomeDocuments")
     * @ORM\JoinColumn(name="firm_contactID_from", referencedColumnName="contactID", nullable=true)
     */
    private $firm_contact_from;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="firmIncomeDocuments")
     * @ORM\JoinColumn(name="firmID", referencedColumnName="firmID", nullable=true)
     */
    private $firm;

    /**
     * @var Osn
     * @ORM\Embedded(class="Osn", columnPrefix="osn_")
     */
    private $osn;

    /**
     * @var Income[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Income\Income", cascade={"persist"}, mappedBy="income_document")
     */
    private $incomes;

    /**
     * @var IncomeGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Good\IncomeGood", cascade={"persist"}, mappedBy="income_document", orphanRemoval=true)
     */
    private $income_goods;

    /**
     * @var OrderGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Good\OrderGood", cascade={"persist"}, mappedBy="incomeDocument")
     */
    private $order_goods;

    /**
     * @var FirmBalanceHistory[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory", mappedBy="incomeDocument")
     */
    private $firm_balance_history;

    /**
     * @var SchetFakKor[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\Expense\Entity\SchetFakKor\SchetFakKor", mappedBy="incomeDocuments")
     */
    private $schet_fak_kors;

    public function __construct(DocumentType $documentType, Document $document, Manager $manager, ?Provider $provider, ?User $user, ?Contact $user_contact, Firm $firm, Osn $osn)
    {
        $this->document_type = $documentType;
        $this->document = $document;
        $this->dateofadded = new \DateTime();
        $this->manager = $manager;
        $this->provider = $provider;
        $this->user = $user;
        $this->user_contact = $user_contact;
        $this->firm = $firm;
        $this->osn = $osn;
        $this->income_goods = new ArrayCollection();
        $this->incomes = new ArrayCollection();
        $this->order_goods = new ArrayCollection();
        $this->firm_balance_history = new ArrayCollection();
        $this->schet_fak_kors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->incomeDocumentID;
    }

    public function getDocumentType(): DocumentType
    {
        return $this->document_type;
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getProvider(): ?Provider
    {
        return $this->provider;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getUserContact(): ?Contact
    {
        return $this->user_contact;
    }

    public function getFirmFrom(): ?Firm
    {
        return $this->firm_from;
    }

    public function getFirmContactFrom(): ?Contact
    {
        return $this->firm_contact_from;
    }

    public function getFirm(): ?Firm
    {
        return $this->firm;
    }

    public function getOsn(): Osn
    {
        return $this->osn;
    }

    /**
     * @return Income[]|ArrayCollection
     */
    public function getIncomes()
    {
        return $this->incomes->toArray();
    }

    /**
     * @return float
     */
    public function getIncomesSum(): float
    {
        $sum = 0;
        foreach ($this->incomes as $income) {
            $sum += $income->getPrice() * $income->getQuantity();
        }
        return $sum;
    }

    public function assignIncomeGood(Income $income, IncomeSklad $incomeSklad, Manager $manager, int $quantity, string $returning_reason): void
    {
        $this->income_goods->add(new IncomeGood($income, $this, $incomeSklad->getZapSklad(), $income->getZapCard(), $manager, $quantity, $returning_reason));
    }

    /**
     * @return IncomeGood[]|ArrayCollection
     */
    public function getIncomeGoods()
    {
        return $this->income_goods;
    }


}
