<?php

namespace App\Model\Sklad\Entity\ZapSklad;

use App\Model\Card\Entity\Abc\ZapCardAbc;
use App\Model\Card\Entity\Abc\ZapCardAbcHistory;
use App\Model\Card\Entity\Location\ZapSkladLocation;
use App\Model\Card\Entity\Reserve\ZapCardReserve;
use App\Model\Card\Entity\ReserveSklad\ZapCardReserveSklad;
use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use App\Model\Expense\Entity\SkladDocument\ExpenseSkladDocument;
use App\Model\Income\Entity\Good\IncomeGood;
use App\Model\Income\Entity\Order\IncomeOrder;
use App\Model\Income\Entity\Sklad\IncomeSklad;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\User\Entity\Opt\Opt;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @ORM\Entity(repositoryClass=ZapSkladRepository::class)
 * @ORM\Table(name="zapSklad")
 */
class ZapSklad
{
    public const OSN_SKLAD_ID = 1;
    public const MSK = 1;
    public const SPB = 5;
    public const SPB2 = 6;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="zapSkladID")
     */
    private $zapSkladID;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $name_short;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean", name="isTorg")
     */
    private $isTorg = true;

    /**
     * @ORM\Column(type="decimal", precision=4, scale=2)
     */
    private $koef;

    /**
     * @var Opt
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\Opt\Opt")
     * @ORM\JoinColumn(name="optID", referencedColumnName="optID", nullable=true)
     */
    private $opt;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @ORM\Column(type="boolean", name="noneDelete")
     */
    private $noneDelete = false;

    /**
     * @var Manager[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\Manager\Entity\Manager\Manager", mappedBy="sklads")
     */
    private $managers;

    /**
     * @var ZapCardAbc[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Abc\ZapCardAbc", mappedBy="zapSklad", orphanRemoval=true)
     */
    private $abc;

    /**
     * @var ZapCardAbcHistory[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Abc\ZapCardAbcHistory", mappedBy="zapSklad", orphanRemoval=true)
     */
    private $abc_history;

    /**
     * @var ZapSkladLocation[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Location\ZapSkladLocation", mappedBy="zapSklad", orphanRemoval=true)
     */
    private $locations;

    /**
     * @var IncomeSklad[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Sklad\IncomeSklad", mappedBy="zapSklad")
     */
    private $income_sklads;

    /**
     * @var IncomeOrder[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Order\IncomeOrder", mappedBy="zapSklad")
     */
    private $income_orders;

    /**
     * @var IncomeGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Good\IncomeGood", mappedBy="zapSklad")
     */
    private $income_goods;

    /**
     * @var OrderGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Good\OrderGood", mappedBy="zapSklad")
     */
    private $order_goods;

    /**
     * @var ExpenseSkladDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\SkladDocument\ExpenseSkladDocument", mappedBy="zapSklad")
     */
    private $expenseSkladDocuments;

    /**
     * @var ExpenseSkladDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\SkladDocument\ExpenseSkladDocument", mappedBy="zapSklad_to")
     */
    private $expenseSkladDocumentsTo;

    /**
     * @var ExpenseSklad[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Sklad\ExpenseSklad", mappedBy="zapSklad")
     */
    private $expense_sklads;

    /**
     * @var ExpenseSklad[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Sklad\ExpenseSklad", mappedBy="zapSklad_to")
     */
    private $expense_sklads_to;

    /**
     * @var ZapCardReserve[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Reserve\ZapCardReserve", mappedBy="zapSklad")
     */
    private $zapCardReserve;

    /**
     * @var ZapCardReserveSklad[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\ReserveSklad\ZapCardReserveSklad", mappedBy="zapSklad")
     */
    private $zapCardReserveSklad;

    /**
     * @var ZapCardReserveSklad[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\ReserveSklad\ZapCardReserveSklad", mappedBy="zapSklad_to")
     */
    private $zapCardReserveSkladTo;

    public function __construct(string $name_short, string $name, bool $isTorg, string $koef, ?Opt $opt, bool $isMain)
    {
        $this->name_short = $name_short;
        $this->name = $name;
        $this->isTorg = $isTorg;
        $this->koef = str_replace(',', '.', $koef);
        $this->opt = $opt;
        $this->isMain = $isMain;
    }

    public function update(string $name_short, string $name, bool $isTorg, string $koef, ?Opt $opt, bool $isMain)
    {
        $this->name_short = $name_short;
        $this->name = $name;
        $this->isTorg = $isTorg;
        $this->koef = str_replace(',', '.', $koef);
        $this->opt = $opt;
        $this->isMain = $isMain;
    }

    public function getId(): ?int
    {
        return $this->zapSkladID;
    }

    public function getNameShort(): string
    {
        return $this->name_short;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isTorg(): ?bool
    {
        return $this->isTorg;
    }

    public function getKoef(): ?string
    {
        return $this->koef;
    }

    public function getOpt(): ?Opt
    {
        return $this->opt;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
    }

    public function setMain(): void
    {
        $this->isMain = true;
    }

    public function getNoneDelete(): ?bool
    {
        return $this->noneDelete;
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unhide(): void
    {
        $this->isHide = false;
    }
}
