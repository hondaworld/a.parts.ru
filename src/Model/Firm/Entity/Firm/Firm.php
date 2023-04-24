<?php

namespace App\Model\Firm\Entity\Firm;

use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Document\Entity\Document\Document;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\SchetFak\SchetFak;
use App\Model\Expense\Entity\SchetFakKor\SchetFakKor;
use App\Model\Expense\Entity\SkladDocument\ExpenseSkladDocument;
use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Model\Finance\Entity\Nalog\Nalog;
use App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory;
use App\Model\Firm\Entity\ManagerFirm\ManagerFirm;
use App\Model\Firm\Entity\Schet\Schet;
use App\Model\Income\Entity\Document\IncomeDocument;
use App\Model\Income\Entity\Income\Income;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FirmRepository::class)
 * @ORM\Table(name="firms")
 */
class Firm
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="firmID")
     */
    private $firmID;

    /**
     * @var Nalog
     * @ORM\ManyToOne(targetEntity="App\Model\Finance\Entity\Nalog\Nalog", inversedBy="firms")
     * @ORM\JoinColumn(name="nalogID", referencedColumnName="nalogID")
     */
    private $nalog;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name_short;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $inn;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $kpp;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $okpo;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $ogrn;

    /**
     * @ORM\Column(type="boolean", name="isNDS")
     */
    private $isNDS = false;

    /**
     * @ORM\Column(type="boolean", name="isUr")
     */
    private $isUr = false;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="director_firms", fetch="EAGER")
     * @ORM\JoinColumn(name="directorID", referencedColumnName="managerID", nullable=true)
     */
    private $director;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="buhgalter_firms", fetch="EAGER")
     * @ORM\JoinColumn(name="buhgalterID", referencedColumnName="managerID", nullable=true)
     */
    private $buhgalter;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

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

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateofrasclosed;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $spis_goods = 'FIFO';

    /**
     * @ORM\Column(type="integer", name="schetDays")
     */
    private $schetDays = 0;

    /**
     * @ORM\Column(type="boolean")
     */
    private $sale_other = true;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sale_name = '';

    /**
     * @ORM\Column(type="boolean")
     */
    private $buy_other = true;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $buy_name = '';

    /**
     * @ORM\Column(type="integer")
     */
    private $first_schet = 1;

    /**
     * @ORM\Column(type="integer")
     */
    private $first_nakladnaya = 1;

    /**
     * @ORM\Column(type="integer")
     */
    private $first_schetfak = 1;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $edo = '';

    /**
     * @var Contact[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Contact\Entity\Contact\Contact", mappedBy="firm", cascade={"persist"}, orphanRemoval=true)
     */
    private $contacts;

    /**
     * @var Beznal[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Beznal\Entity\Beznal\Beznal", mappedBy="firm", cascade={"persist"}, orphanRemoval=true)
     */
    private $beznals;

    /**
     * @var Document[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Document\Entity\Document\Document", mappedBy="firm", cascade={"persist"}, orphanRemoval=true)
     */
    private $documents;

    /**
     * @var ManagerFirm[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\ManagerFirm\ManagerFirm", mappedBy="firm", orphanRemoval=true)
     */
    private $manager_firms;

    /**
     * @var UserBalanceHistory[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\BalanceHistory\UserBalanceHistory", mappedBy="firm")
     */
    private $balance_history;

    /**
     * @var IncomeDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Document\IncomeDocument", mappedBy="firm_from")
     */
    private $firmFromIncomeDocuments;

    /**
     * @var IncomeDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Document\IncomeDocument", mappedBy="firm")
     */
    private $firmIncomeDocuments;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="firm")
     */
    private $expenseDocuments;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="exp_firm")
     */
    private $expenseDocumentsExp;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="gruz_firm")
     */
    private $expenseDocumentsGruz;

    /**
     * @var Income[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Income\Income", mappedBy="firm")
     */
    private $incomes;

    /**
     * @var Schet[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\Schet\Schet", mappedBy="firm")
     */
    private $schets;

    /**
     * @var ExpenseSkladDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\SkladDocument\ExpenseSkladDocument", mappedBy="firm")
     */
    private $expenseSkladDocuments;

    /**
     * @var FirmBalanceHistory[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory", cascade={"persist"}, mappedBy="firm", orphanRemoval=true)
     */
    private $firm_balance_history;

    /**
     * @var FinanceType[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Finance\Entity\FinanceType\FinanceType", mappedBy="firm")
     */
    private $finance_types;

    /**
     * @var SchetFak[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\SchetFak\SchetFak", mappedBy="firm")
     */
    private $schet_faks;

    /**
     * @var SchetFakKor[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\SchetFakKor\SchetFakKor", mappedBy="firm")
     */
    private $schet_fak_kors;

    public function __construct(
        string   $name_short,
        string   $name,
        ?string  $inn,
        ?string  $kpp,
        ?string  $okpo,
        ?string  $ogrn,
        bool     $isNDS,
        bool     $isUr,
        Nalog    $nalog,
        ?Manager $director,
        ?Manager $buhgalter
    )
    {
        $this->name_short = $name_short;
        $this->name = $name;
        $this->inn = $inn ?: '';
        $this->kpp = $kpp ?: '';
        $this->okpo = $okpo ?: '';
        $this->ogrn = $ogrn ?: '';
        $this->isNDS = $isNDS;
        $this->isUr = $isUr;
        $this->nalog = $nalog;
        $this->director = $director;
        $this->buhgalter = $buhgalter;
        $this->dateofadded = new \DateTime();
        $this->documents = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->beznals = new ArrayCollection();
        $this->firm_balance_history = new ArrayCollection();
    }

    public function update(
        string     $name_short,
        string     $name,
        ?string    $inn,
        ?string    $kpp,
        ?string    $okpo,
        ?string    $ogrn,
        bool       $isNDS,
        bool       $isUr,
        Nalog      $nalog,
        ?Manager   $director,
        ?Manager   $buhgalter,
        \DateTime  $dateofadded,
        ?\DateTime $dateofclosed
    )
    {
        $this->name_short = $name_short;
        $this->name = $name;
        $this->inn = $inn ?: '';
        $this->kpp = $kpp ?: '';
        $this->okpo = $okpo ?: '';
        $this->ogrn = $ogrn ?: '';
        $this->isNDS = $isNDS;
        $this->isUr = $isUr;
        $this->nalog = $nalog;
        $this->director = $director;
        $this->buhgalter = $buhgalter;
        $this->dateofadded = $dateofadded;
        $this->dateofclosed = $dateofclosed;
    }

    public function updateOthers(int $first_schet, int $first_nakladnaya, int $first_schetfak)
    {
        $this->first_schet = $first_schet;
        $this->first_nakladnaya = $first_nakladnaya;
        $this->first_schetfak = $first_schetfak;
    }

    public function getId(): ?int
    {
        return $this->firmID;
    }

    public function getNalog(): Nalog
    {
        return $this->nalog;
    }

    public function getNDS(float $sum): float
    {
        if (!$this->isNDS()) {
            return 0;
        } else {
            $nds = $this->getNalog()->getLastNds() ? $this->getNalog()->getLastNds()->getNds() : 0;
            return round($sum / (100 + $nds) * $nds * 100) / 100;
        }
    }

    public function getNameShort(): ?string
    {
        return $this->name_short;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNameWithInnAndKpp(): string
    {
        $name = $this->getName();
        if ($this->getInn()) {
            $name .= ', ИНН ' . $this->getInn();
        }
        if ($this->getKpp()) {
            $name .= ', КПП ' . $this->getKpp();
        }
        return $name;
    }

    public function getInn(): string
    {
        return $this->inn;
    }

    public function getKpp(): string
    {
        return $this->kpp;
    }

    public function getOkpo(): string
    {
        return $this->okpo;
    }

    public function getOgrn(): string
    {
        return $this->ogrn;
    }

    public function isNDS(): ?bool
    {
        return $this->isNDS;
    }

    public function isUr(): ?bool
    {
        return $this->isUr;
    }

    public function getDirector(): ?Manager
    {
        return $this->director;
    }

    public function removeDirector()
    {
        $this->director = null;
    }

    public function getBuhgalter(): ?Manager
    {
        return $this->buhgalter;
    }

    public function removeBuhgalter()
    {
        $this->buhgalter = null;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
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

    public function getDateofadded(): \DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getDateofclosed(): ?\DateTimeInterface
    {
        if ($this->dateofclosed && $this->dateofclosed->format('Y') == '-0001') return null;
        return $this->dateofclosed;
    }

    public function getDateofrasclosed(): ?\DateTimeInterface
    {
        if ($this->dateofrasclosed && $this->dateofrasclosed->format('Y') == '-0001') return null;
        return $this->dateofrasclosed;
    }

    public function getSpisGoods(): ?string
    {
        return $this->spis_goods;
    }

    public function getSchetDays(): ?int
    {
        return $this->schetDays;
    }

    public function getSaleOther(): ?int
    {
        return $this->sale_other;
    }

    public function getSaleName(): ?string
    {
        return $this->sale_name;
    }

    public function getBuyOther(): ?int
    {
        return $this->buy_other;
    }

    public function getBuyName(): ?string
    {
        return $this->buy_name;
    }

    public function getFirstSchet(): ?int
    {
        return $this->first_schet;
    }

    public function getFirstNakladnaya(): ?int
    {
        return $this->first_nakladnaya;
    }

    public function getFirstSchetfak(): ?int
    {
        return $this->first_schetfak;
    }

    public function getEdo(): ?string
    {
        return $this->edo;
    }

    /**
     * @return ManagerFirm[]|ArrayCollection
     */
    public function getManagerFirms()
    {
        return $this->manager_firms->toArray();
    }

    /**
     * @return UserBalanceHistory[]|array
     */
    public function getBalanceHistory(): array
    {
        return $this->balance_history->toArray();
    }

    /**
     * @return FirmBalanceHistory[]|ArrayCollection
     */
    public function getFirmBalanceHistory()
    {
        return $this->firm_balance_history;
    }

    public function assignFirmBalanceHistory(Provider $provider, string $sum, ?string $nds, Manager $manager, ?string $description, ?IncomeDocument $incomeDocument): void
    {
        $this->firm_balance_history->add(new FirmBalanceHistory($provider, $provider->getUser(), $sum, $nds ?: 0, $manager, $description, $incomeDocument, $this));
    }

    /**
     * @return FinanceType[]|ArrayCollection
     */
    public function getFinanceTypes()
    {
        return $this->finance_types->toArray();
    }

    public function getFinanceTypeNames(): array
    {
        if (!$this->finance_types) return [];
        return array_map(function ($item) {
            return $item->getName();
        }, $this->getFinanceTypes());
    }

    /**
     * @return Contact[]|ArrayCollection
     */
    public function getContacts()
    {
        return $this->contacts->toArray();
    }

    public function checkIsMainContact(bool $isMain, ?Contact $contact = null): bool
    {
        if (!$isMain && $contact && $contact->isMain()) {
            throw new \DomainException("Должен быть хоть один основной контакт");
        }

        if (!$isMain && !$this->getMainContact()) {
            $isMain = true;
        }

        if ($isMain) {
            $this->clearMainContacts();
        }

        return $isMain;
    }

    public function getMainContact(): ?Contact
    {
        $contacts = $this->getContacts();
        usort($contacts, function (Contact $a, Contact $b) {
            if ($a->isMain() == $b->isMain()) {
                return $a->getId() <=> $b->getId();
            }
            return $b->isMain() <=> $a->isMain();
        });
        return $contacts ? $contacts[0] : null;
    }

    public function clearMainContacts(): void
    {
        foreach ($this->contacts as $contact) {
            $contact->clearMain();
        }
    }

    public function assignContact(Contact $contact): void
    {
        $this->contacts->add($contact);
    }

    /**
     * @return Beznal[]|ArrayCollection
     */
    public function getBeznals()
    {
        return $this->beznals->toArray();
    }

    public function checkIsMainBeznal(bool $isMain, ?Beznal $beznal = null): bool
    {
        if (!$isMain && $beznal && $beznal->isMain()) {
            throw new \DomainException("Должен быть хоть один основной реквизит");
        }

        if (!$isMain && !$this->getMainBeznal()) {
            $isMain = true;
        }

        if ($isMain) {
            $this->clearMainBeznals();
        }

        return $isMain;
    }

    public function getMainBeznal(): ?Beznal
    {
        $beznals = $this->getBeznals();
        usort($beznals, function (Beznal $a, Beznal $b) {
            if ($a->isMain() == $b->isMain()) {
                return $a->getId() <=> $b->getId();
            }
            return $b->isMain() <=> $a->isMain();
        });
        return $beznals ? $beznals[0] : null;
    }

    public function clearMainBeznals(): void
    {
        foreach ($this->beznals as $beznal) {
            $beznal->clearMain();
        }
    }

    public function assignBeznal(Beznal $beznal): void
    {
        $this->beznals->add($beznal);
    }

    /**
     * @return Document[]|ArrayCollection
     */
    public function getDocuments()
    {
        return $this->documents->toArray();
    }

    public function checkIsMainDocument(bool $isMain): bool
    {
        if ($isMain) {
            $this->clearMainDocuments();
        }

        return $isMain;
    }

    public function clearMainDocuments(): void
    {
        foreach ($this->documents as $document) {
            $document->clearMain();
        }
    }

    public function assignDocument(Document $document): void
    {
        $this->documents->add($document);
    }
}
