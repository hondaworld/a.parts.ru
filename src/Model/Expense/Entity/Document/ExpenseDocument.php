<?php

namespace App\Model\Expense\Entity\Document;

use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Expense\Entity\DocumentPrint\ExpenseDocumentPrint;
use App\Model\Expense\Entity\SchetFak\SchetFak;
use App\Model\Expense\Entity\Shipping\Shipping;
use App\Model\Expense\Entity\ShippingStatus\ShippingStatus;
use App\Model\Expense\Entity\Type\ExpenseType;
use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Check\Check;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Shop\Entity\Reseller\Reseller;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
use App\Model\User\Entity\FirmContr\FirmContr;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExpenseDocumentRepository::class)
 * @ORM\Table(name="expenseDocuments")
 */
class ExpenseDocument
{
    public const STATUS_NEW = 0;
    public const STATUS_DONE = 2;

    public const PICK_NONE = 0;
    public const PICKING = 1;
    public const PICKED = 2;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="expenseDocumentID")
     */
    private $expenseDocumentID;

    /**
     * @var ExpenseType
     * @ORM\ManyToOne(targetEntity="App\Model\Expense\Entity\Type\ExpenseType")
     * @ORM\JoinColumn(name="expense_type_id", referencedColumnName="id", nullable=true)
     */
    private $expense_type;

    /**
     * @var FinanceType
     * @ORM\ManyToOne(targetEntity="App\Model\Finance\Entity\FinanceType\FinanceType", inversedBy="expenseDocuments")
     * @ORM\JoinColumn(name="finance_typeID", referencedColumnName="finance_typeID", nullable=true)
     */
    private $finance_type;

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
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="expenseDocuments")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=true)
     */
    private $manager;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = 0;

    /**
     * @ORM\Column(type="integer", name="isShipping")
     */
    private $isShipping = 0;

    /**
     * @var DocumentType
     * @ORM\ManyToOne(targetEntity="App\Model\Document\Entity\Type\DocumentType", inversedBy="expenseDocuments")
     * @ORM\JoinColumn(name="doc_typeID", referencedColumnName="doc_typeID", nullable=true)
     */
    private $document_type;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="expenseDocuments")
     * @ORM\JoinColumn(name="userID", referencedColumnName="userID", nullable=true)
     */
    private $user;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="expenseDocuments")
     * @ORM\JoinColumn(name="firmID", referencedColumnName="firmID", nullable=true)
     */
    private $firm;

    /**
     * @var Osn
     * @ORM\Embedded(class="Osn", columnPrefix="osn_")
     */
    private $osn;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="expenseDocumentsExp")
     * @ORM\JoinColumn(name="exp_userID", referencedColumnName="userID", nullable=true)
     */
    private $exp_user;

    /**
     * @var Contact
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Contact\Contact", inversedBy="expenseDocumentsExpUser")
     * @ORM\JoinColumn(name="exp_user_contactID ", referencedColumnName="contactID", nullable=true)
     */
    private $exp_user_contact;

    /**
     * @var Beznal
     * @ORM\ManyToOne(targetEntity="App\Model\Beznal\Entity\Beznal\Beznal", inversedBy="expenseDocumentsExpUser")
     * @ORM\JoinColumn(name="exp_user_beznalID  ", referencedColumnName="beznalID", nullable=true)
     */
    private $exp_user_beznal;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="expenseDocumentsGruz")
     * @ORM\JoinColumn(name="gruz_userID", referencedColumnName="userID", nullable=true)
     */
    private $gruz_user;

    /**
     * @var Contact
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Contact\Contact", inversedBy="expenseDocumentsGruzUser")
     * @ORM\JoinColumn(name="gruz_user_contactID ", referencedColumnName="contactID", nullable=true)
     */
    private $gruz_user_contact;

    /**
     * @var Beznal
     * @ORM\ManyToOne(targetEntity="App\Model\Beznal\Entity\Beznal\Beznal", inversedBy="expenseDocumentsGruzUser")
     * @ORM\JoinColumn(name="gruz_user_beznalID  ", referencedColumnName="beznalID", nullable=true)
     */
    private $gruz_user_beznal;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="expenseDocumentsCash")
     * @ORM\JoinColumn(name="cash_userID", referencedColumnName="userID", nullable=true)
     */
    private $cash_user;

    /**
     * @var Contact
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Contact\Contact", inversedBy="expenseDocumentsCashUser")
     * @ORM\JoinColumn(name="cash_user_contactID ", referencedColumnName="contactID", nullable=true)
     */
    private $cash_user_contact;

    /**
     * @var Beznal
     * @ORM\ManyToOne(targetEntity="App\Model\Beznal\Entity\Beznal\Beznal", inversedBy="expenseDocumentsCashUser")
     * @ORM\JoinColumn(name="cash_user_beznalID  ", referencedColumnName="beznalID", nullable=true)
     */
    private $cash_user_beznal;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="expenseDocumentsExp")
     * @ORM\JoinColumn(name="exp_firmID", referencedColumnName="firmID", nullable=true)
     */
    private $exp_firm;

    /**
     * @var Contact
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Contact\Contact", inversedBy="expenseDocumentsExpFirm")
     * @ORM\JoinColumn(name="exp_firm_contactID ", referencedColumnName="contactID", nullable=true)
     */
    private $exp_firm_contact;

    /**
     * @var Beznal
     * @ORM\ManyToOne(targetEntity="App\Model\Beznal\Entity\Beznal\Beznal", inversedBy="expenseDocumentsExpFirm")
     * @ORM\JoinColumn(name="exp_firm_beznalID  ", referencedColumnName="beznalID", nullable=true)
     */
    private $exp_firm_beznal;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="expenseDocumentsGruz")
     * @ORM\JoinColumn(name="gruz_firmID", referencedColumnName="firmID", nullable=true)
     */
    private $gruz_firm;

    /**
     * @var Contact
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Contact\Contact", inversedBy="expenseDocumentsGruzFirm")
     * @ORM\JoinColumn(name="gruz_firm_contactID ", referencedColumnName="contactID", nullable=true)
     */
    private $gruz_firm_contact;

    /**
     * @var Beznal
     * @ORM\ManyToOne(targetEntity="App\Model\Beznal\Entity\Beznal\Beznal", inversedBy="expenseDocumentsGruzFirm")
     * @ORM\JoinColumn(name="gruz_firm_beznalID  ", referencedColumnName="beznalID", nullable=true)
     */
    private $gruz_firm_beznal;

    /**
     * @var FirmContr
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\FirmContr\FirmContr", inversedBy="expenseDocumentsGruz")
     * @ORM\JoinColumn(name="gruz_firmcontrID", referencedColumnName="firmcontrID", nullable=true)
     */
    private $gruz_firmcontr;

    /**
     * @var FirmContr
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\FirmContr\FirmContr", inversedBy="expenseDocumentsCash")
     * @ORM\JoinColumn(name="cash_firmcontrID", referencedColumnName="firmcontrID", nullable=true)
     */
    private $cash_firmcontr;

    /**
     * @ORM\Column(type="boolean", name="isGruzInnKpp")
     */
    private $isGruzInnKpp = false;

    /**
     * @ORM\Column(type="integer", name="isUserEmail")
     */
    private $isUserEmail = 3;

    /**
     * @ORM\Column(type="boolean", name="isService")
     */
    private $isService = false;

    /**
     * @ORM\Column(type="string", length=4)
     */
    private $sms_code = '';

    /**
     * @ORM\Column(type="boolean", name="isSmsCheck")
     */
    private $isSmsCheck = false;

    /**
     * @var Reseller
     * @ORM\ManyToOne(targetEntity="App\Model\Shop\Entity\Reseller\Reseller", inversedBy="expenseDocuments")
     * @ORM\JoinColumn(name="reseller_id", referencedColumnName="id", nullable=true)
     */
    private $reseller;

    /**
     * @var OrderGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Good\OrderGood", mappedBy="expenseDocument")
     */
    private $order_goods;

    /**
     * @var UserBalanceHistory[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\BalanceHistory\UserBalanceHistory", mappedBy="expenseDocument")
     */
    private $balance_history;

    /**
     * @var Shipping[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Shipping\Shipping", mappedBy="expenseDocument", cascade={"persist"})
     */
    private $shippings;

    /**
     * @var Check
     * @ORM\OneToOne(targetEntity="App\Model\Order\Entity\Check\Check", mappedBy="expenseDocument")
     */
    private $check;

    /**
     * @var SchetFak
     * @ORM\OneToOne(targetEntity="App\Model\Expense\Entity\SchetFak\SchetFak", mappedBy="expenseDocument")
     */
    private $schet_fak;

    /**
     * @var ExpenseDocumentPrint
     * @ORM\OneToOne(targetEntity="App\Model\Expense\Entity\DocumentPrint\ExpenseDocumentPrint", mappedBy="expenseDocument")
     */
    private $expenseDocumentPrint;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->exp_user = $user;
        $this->exp_user_contact = $user->getMainContact();
        $this->exp_user_beznal = $user->getMainBeznal();
        $this->gruz_user = $user->getGruzUser();
        $this->gruz_user_contact = $user->getGruzUserContact();
        $this->gruz_user_beznal = $user->getGruzUserBeznal();
        $this->gruz_firmcontr = $user->getGruzFirmContr();
        $this->cash_user = $user->getCashUser();
        $this->cash_user_contact = $user->getCashUserContact();
        $this->cash_user_beznal = $user->getCashUserBeznal();
        $this->cash_firmcontr = $user->getCashFirmContr();
        $this->isGruzInnKpp = $user->isGruzInnKpp();
        $this->osn = new Osn();
        $this->document = new Document();
        $this->status = ExpenseDocument::STATUS_NEW;
        $this->shippings = new ArrayCollection();
    }

    public function done(Document $document, DocumentType $documentType, Manager $manager, bool $isService)
    {
        $this->document = $document;
        $this->document_type = $documentType;
        $this->manager = $manager;
        $this->isService = $isService;
        $this->status = ExpenseDocument::STATUS_DONE;
    }

    public function updateExpUser(?User $user, ?Contact $contact, ?Beznal $beznal)
    {
        $this->exp_user = $user;
        $this->exp_user_contact = $contact;
        $this->exp_user_beznal = $beznal;
    }

    public function reNewDateOfAdded()
    {
        $this->dateofadded = new \DateTime();
    }

    public function updateFirm(?Firm $firm)
    {
        $this->firm = $firm;
    }

    public function updateExpFirm(?Firm $firm, ?Contact $contact, ?Beznal $beznal)
    {
        $this->exp_firm = $firm;
        $this->exp_firm_contact = $contact;
        $this->exp_firm_beznal = $beznal;
    }

    public function updateCashier(?User $user, ?Contact $contact, ?Beznal $beznal)
    {
        if ($user != null) {
            $this->cash_firmcontr = null;
        }
        $this->cash_user = $user;
        $this->cash_user_contact = $contact;
        $this->cash_user_beznal = $beznal;
    }

    public function updateCashierFirmContr(?FirmContr $firmContr)
    {
        if ($firmContr != null) {
            $this->cash_user = null;
            $this->cash_user_contact = null;
            $this->cash_user_beznal = null;
        }
        $this->cash_firmcontr = $firmContr;
    }

    public function updateCashierSchetFak(bool $isGruzInnKpp)
    {
        $this->isGruzInnKpp = $isGruzInnKpp;
    }

    public function updateGetter(?User $user, ?Contact $contact, ?Beznal $beznal)
    {
        if ($user != null) {
            $this->gruz_firmcontr = null;
        }
        $this->gruz_user = $user;
        $this->gruz_user_contact = $contact;
        $this->gruz_user_beznal = $beznal;
    }

    public function updateGetterFirmContr(?FirmContr $firmContr)
    {
        if ($firmContr != null) {
            $this->gruz_user = null;
            $this->gruz_user_contact = null;
            $this->gruz_user_beznal = null;
        }
        $this->gruz_firmcontr = $firmContr;
    }

    public function updateGruzFirm(?Firm $firm, ?Contact $contact, ?Beznal $beznal)
    {
        $this->gruz_firm = $firm;
        $this->gruz_firm_contact = $contact;
        $this->gruz_firm_beznal = $beznal;
    }

    public function updateFinanceData(?FinanceType $financeType, ?ExpenseType $expenseType, ?Reseller $reseller)
    {
        $this->finance_type = $financeType;
        $this->expense_type = $expenseType;
        $this->reseller = $reseller;
    }

    public function updateReseller(?Reseller $reseller)
    {
        $this->reseller = $reseller;
    }

    public function generateSmsCode()
    {
        $this->sms_code = mt_rand(1000, 9999);
    }

    public function smsCodeChecked()
    {
        $this->isSmsCheck = true;
    }

    public function picking(): void
    {
        $this->isShipping = self::PICKING;
    }

    public function picked(): void
    {
        $this->isShipping = self::PICKED;
    }

    public function pickDelete(): void
    {
        $this->isShipping = self::PICK_NONE;
    }

    public function allowSentDocument(): void
    {
        $this->isUserEmail = 1;
    }

    public function documentsSent(): void
    {
        $this->isUserEmail = 3;
    }

    public function getId(): ?int
    {
        return $this->expenseDocumentID;
    }

    public function getExpenseType(): ?ExpenseType
    {
        return $this->expense_type;
    }

    public function getFinanceType(): ?FinanceType
    {
        return $this->finance_type;
    }

    /**
     * @return \DateTime
     */
    public function getDateofadded(): ?\DateTimeInterface
    {
        if ($this->dateofadded && $this->dateofadded->format('Y') == '-0001') return null;
        return $this->dateofadded;
    }

    /**
     * @return DocumentType
     */
    public function getDocumentType(): DocumentType
    {
        return $this->document_type;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return Firm
     */
    public function getFirm(): ?Firm
    {
        return $this->firm;
    }

    /**
     * @return User
     */
    public function getExpUser(): ?User
    {
        return $this->exp_user;
    }

    /**
     * @return Contact
     */
    public function getExpUserContact(): ?Contact
    {
        return $this->exp_user_contact;
    }

    /**
     * @return Beznal
     */
    public function getExpUserBeznal(): ?Beznal
    {
        return $this->exp_user_beznal;
    }

    /**
     * @return User
     */
    public function getGruzUser(): ?User
    {
        return $this->gruz_user;
    }

    /**
     * @return Contact
     */
    public function getGruzUserContact(): ?Contact
    {
        return $this->gruz_user_contact;
    }

    /**
     * @return Beznal
     */
    public function getGruzUserBeznal(): ?Beznal
    {
        return $this->gruz_user_beznal;
    }

    /**
     * @return User
     */
    public function getGruzUserForDocument(): ?User
    {
        return $this->gruz_user ?: $this->exp_user;
    }

    /**
     * @return Contact
     */
    public function getGruzUserContactForDocument(): ?Contact
    {
        if ($this->gruz_user)
            return $this->gruz_user_contact;
        else
            return $this->exp_user_contact;
    }

    /**
     * @return Beznal
     */
    public function getGruzUserBeznalForDocument(): ?Beznal
    {
        if ($this->gruz_user)
            return $this->gruz_user_beznal;
        else
            return $this->exp_user_beznal;
    }

    /**
     * @return User
     */
    public function getCashUser(): ?User
    {
        return $this->cash_user;
    }

    /**
     * @return Contact
     */
    public function getCashUserContact(): ?Contact
    {
        return $this->cash_user_contact;
    }

    /**
     * @return Beznal
     */
    public function getCashUserBeznal(): ?Beznal
    {
        return $this->cash_user_beznal;
    }

    /**
     * @return User
     */
    public function getCashUserForDocument(): ?User
    {
        if ($this->cash_user)
            return $this->cash_user;
        else
            return $this->exp_user;
    }

    /**
     * @return Contact
     */
    public function getCashUserContactForDocument(): ?Contact
    {
        if ($this->cash_user_contact)
            return $this->cash_user_contact;
        else
            return $this->exp_user_contact;
    }

    /**
     * @return Beznal
     */
    public function getCashUserBeznalForDocument(): ?Beznal
    {
        if ($this->cash_user_beznal)
            return $this->cash_user_beznal;
        else
            return $this->exp_user_beznal;
    }

    /**
     * @return Firm
     */
    public function getExpFirm(): ?Firm
    {
        return $this->exp_firm;
    }

    /**
     * @return Contact
     */
    public function getExpFirmContact(): ?Contact
    {
        return $this->exp_firm_contact;
    }

    /**
     * @return Beznal
     */
    public function getExpFirmBeznal(): ?Beznal
    {
        return $this->exp_firm_beznal;
    }

    /**
     * @return Firm
     */
    public function getGruzFirm(): ?Firm
    {
        return $this->gruz_firm;
    }

    /**
     * @return Contact
     */
    public function getGruzFirmContact(): ?Contact
    {
        return $this->gruz_firm_contact;
    }

    /**
     * @return Beznal
     */
    public function getGruzFirmBeznal(): ?Beznal
    {
        return $this->gruz_firm_beznal;
    }

    /**
     * @return Firm
     */
    public function getGruzFirmForDocument(): ?Firm
    {
        return $this->gruz_firm ?: $this->exp_firm;
    }

    /**
     * @return Beznal
     */
    public function getGruzFirmBeznalForDocument(): ?Beznal
    {
        if ($this->gruz_firm)
            return $this->gruz_firm_beznal;
        else
            return $this->exp_firm_beznal;
    }

    /**
     * @return Contact
     */
    public function getGruzFirmContactForDocument(): ?Contact
    {
        if ($this->gruz_firm)
            return $this->gruz_firm_contact;
        else
            return $this->exp_firm_contact;
    }

    /**
     * @return FirmContr
     */
    public function getGruzFirmcontr(): ?FirmContr
    {
        return $this->gruz_firmcontr;
    }

    /**
     * @return FirmContr
     */
    public function getCashFirmcontr(): ?FirmContr
    {
        return $this->cash_firmcontr;
    }

    /**
     * @return bool
     */
    public function isGruzInnKpp(): bool
    {
        return $this->isGruzInnKpp;
    }

    /**
     * @return Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * @return Osn
     */
    public function getOsn(): Osn
    {
        return $this->osn;
    }

    public function isPick(): bool
    {
        return $this->isShipping != self::PICK_NONE;
    }

    public function isPicking(): bool
    {
        return $this->isShipping == self::PICKING;
    }

    public function isPicked(): bool
    {
        return $this->isShipping == self::PICKED;
    }

    /**
     * @return bool
     */
    public function isSmsCheck(): bool
    {
        return $this->isSmsCheck;
    }

    public function getOrCreateShipping(ShippingStatus $status): Shipping
    {
        if ($this->shippings->count() == 0) $this->assignShipping($status);
        return $this->shippings[0];
    }

    public function assignShipping(ShippingStatus $status)
    {
        $this->shippings->add(new Shipping($this, $status));
    }

    public function updateShippingsStatus(ShippingStatus $status)
    {
        foreach ($this->shippings as $shipping) {
            $shipping->updateStatus($status);
        }
    }

    /**
     * @return Shipping[]|ArrayCollection
     */
    public function getShippings()
    {
        return $this->shippings->toArray();
    }

    /**
     * @return OrderGood[]|ArrayCollection
     */
    public function getOrderGoods()
    {
        return $this->order_goods;
    }

    public function getGoodsSum(): float
    {
        $sum = 0;
        foreach ($this->order_goods as $orderGood) {
            $sum += $orderGood->getDiscountPrice() * $orderGood->getQuantity();
        }
        return $sum;
    }

    /**
     * @return string
     */
    public function getSmsCode(): string
    {
        return $this->sms_code;
    }

    public function isSimpleCheck(): ?bool
    {
        if (!$this->finance_type) return null;
        return !in_array($this->finance_type->getId(), [FinanceType::DEFAULT_BEZNAL_ID, FinanceType::DEFAULT_BEZNAL_CARD_ID]);
    }

    public function isBeznal(): ?bool
    {
        if (!$this->finance_type) return null;
        return $this->finance_type->getId() == FinanceType::DEFAULT_BEZNAL_ID;
    }

    /**
     * @return SchetFak|null
     */
    public function getSchetFak(): ?SchetFak
    {
        return $this->schet_fak;
    }

    /**
     * @return ExpenseDocumentPrint|null
     */
    public function getExpenseDocumentPrint(): ?ExpenseDocumentPrint
    {
        return $this->expenseDocumentPrint;
    }

    /**
     * @return Check|null
     */
    public function getCheck(): ?Check
    {
        return $this->check;
    }

    /**
     * @return Reseller|null
     */
    public function getReseller(): ?Reseller
    {
        return $this->reseller;
    }

}
