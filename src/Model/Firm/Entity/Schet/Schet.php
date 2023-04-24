<?php

namespace App\Model\Firm\Entity\Schet;

use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\Entity\SchetGood\SchetGood;
use App\Model\Order\Entity\AlertType\OrderAlertType;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SchetRepository::class)
 * @ORM\Table(name="schet")
 */
class Schet
{
    public const NEW = 0;
    public const NOT_PAID = 1;
    public const PAID = 2;
    public const CANCELED = 3;
    public const STATUSES = [
        self::NOT_PAID => 'Ожидание оплаты',
        self::PAID => 'Оплачен',
        self::CANCELED => 'Отказ',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="schetID")
     */
    private $schetID;

    /**
     * @var FinanceType
     * @ORM\ManyToOne(targetEntity="App\Model\Finance\Entity\FinanceType\FinanceType", inversedBy="schets")
     * @ORM\JoinColumn(name="finance_typeID", referencedColumnName="finance_typeID", nullable=false)
     */
    private $finance_type;

    /**
     * @var Document
     * @ORM\Embedded(class="Document", columnPrefix=false)
     */
    private $document;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateofpaid;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="schets")
     * @ORM\JoinColumn(name="userID", referencedColumnName="userID", nullable=true)
     */
    private $user;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="schetsExp")
     * @ORM\JoinColumn(name="exp_userID", referencedColumnName="userID", nullable=true)
     */
    private $exp_user;

    /**
     * @var Contact
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Contact\Contact", inversedBy="schetsExpUser")
     * @ORM\JoinColumn(name="exp_user_contactID ", referencedColumnName="contactID", nullable=true)
     */
    private $exp_user_contact;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="schets")
     * @ORM\JoinColumn(name="firmID", referencedColumnName="firmID", nullable=true)
     */
    private $firm;

    /**
     * @var Contact
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Contact\Contact", inversedBy="schetsFirm")
     * @ORM\JoinColumn(name="firm_contactID ", referencedColumnName="contactID", nullable=true)
     */
    private $firm_contact;

    /**
     * @var Beznal
     * @ORM\ManyToOne(targetEntity="App\Model\Beznal\Entity\Beznal\Beznal", inversedBy="schetsFirm")
     * @ORM\JoinColumn(name="firm_beznalID  ", referencedColumnName="beznalID", nullable=true)
     */
    private $firm_beznal;

    /**
     * @ORM\Column(type="integer")
     */
    private $status = self::NEW;

    /**
     * @ORM\Column(type="string", length=400)
     */
    private $comment = '';

    /**
     * @ORM\Column(type="string", length=255, name="cancelReason")
     */
    private $cancelReason = '';

    /**
     * @ORM\Column(type="integer")
     */
    private $oplata = 0;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $summ = 0;

    /**
     * @ORM\Column(type="boolean", name="isHideNumbers")
     */
    private $isHideNumbers = false;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $pay_url = '';

    /**
     * @var OrderGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Good\OrderGood", mappedBy="schet", cascade={"persist"})
     */
    private $order_goods;

    /**
     * @var SchetGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\SchetGood\SchetGood", mappedBy="schet", cascade={"persist"}, orphanRemoval=true)
     */
    private $schet_goods;

    /**
     * @var UserBalanceHistory[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\BalanceHistory\UserBalanceHistory", mappedBy="schet")
     */
    private $balance_history;

    public function __construct(FinanceType $finance_type, User $user, ?Firm $firm)
    {
        $this->document = new Document();
        $this->finance_type = $finance_type;
        $this->user = $user;
        $this->firm = $firm;
        $this->order_goods = new ArrayCollection();
        $this->schet_goods = new ArrayCollection();
    }

    public function fromNewToNotPaid(int $schet_num, ?string $document_prefix, ?string $document_sufix, ExpenseDocument $expenseDocument)
    {
        $this->status = self::NOT_PAID;
        $this->document->update($schet_num, $document_prefix, $document_sufix);
        $this->firm = $expenseDocument->getExpFirm();
        $this->firm_contact = $expenseDocument->getExpFirmContact();
        $this->firm_beznal = $expenseDocument->getExpFirmBeznal();
        $this->exp_user = $expenseDocument->getExpUser();
        $this->exp_user_contact = $expenseDocument->getExpUserContact();
        $this->dateofadded = new \DateTime();
    }

    public function fromNewToNotPaidForCreditCard(int $schet_num, ?string $document_prefix, ?string $document_sufix, Firm $firm, User $user, FinanceType $financeType)
    {
        $this->status = self::NOT_PAID;
        $this->document->update($schet_num, $document_prefix, $document_sufix);
        $this->firm = $firm;
        $this->exp_user = $user;
        $this->finance_type = $financeType;
        $this->dateofadded = new \DateTime();
    }

    public function updateDocumentDate(\DateTime $dateofadded, ?string $comment)
    {
        $this->dateofadded = $dateofadded;
        $this->comment = $comment ?: '';
    }

    public function pay(\DateTime $dateofpaid, string $summ, OrderAlertType $orderAlertType)
    {
        $this->status = self::PAID;
        $this->dateofpaid = $dateofpaid;
        $this->summ = $summ;

        foreach ($this->getSchetGoods() as $schetGood) {
            $schetGood->getOrderGood()->assignAlert($orderAlertType);
        }
    }

    public function updatePayUrl(?string $pay_url)
    {
        $this->pay_url = $pay_url ?: '';
    }

    public function cancel(string $cancelReason)
    {
        $this->status = self::CANCELED;
        $this->cancelReason = $cancelReason;
    }

    public function updateUserContact(Contact $userContact)
    {
        $this->exp_user_contact = $userContact;
    }

    public function getId(): ?int
    {
        return $this->schetID;
    }

    public function getFinanceType(): FinanceType
    {
        return $this->finance_type;
    }

    public function getDateofadded(): ?\DateTime
    {
        if ($this->dateofadded && $this->dateofadded->format('Y') == '-0001') return null;
        return $this->dateofadded;
    }

    public function getDateofpaid(): ?\DateTime
    {
        if ($this->dateofpaid && $this->dateofpaid->format('Y') == '-0001') return null;
        return $this->dateofpaid;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getClassByStatus(): ?string
    {
        if ($this->getStatus() == self::NOT_PAID) return 'warning';
        if ($this->getStatus() == self::PAID) return 'success';
        if ($this->getStatus() == self::CANCELED) return 'danger';
        return null;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getCancelReason(): ?string
    {
        return $this->cancelReason;
    }

    public function getOplata(): ?int
    {
        return $this->oplata;
    }

    public function getSumm(): ?string
    {
        return $this->summ;
    }

    public function isHideNumbers(): ?bool
    {
        return $this->isHideNumbers;
    }

    /**
     * @return Document
     */
    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * @return User
     */
    public function getExpUser(): User
    {
        return $this->exp_user;
    }

    /**
     * @return Contact|null
     */
    public function getExpUserContact(): ?Contact
    {
        return $this->exp_user_contact;
    }

    /**
     * @return Firm|null
     */
    public function getFirm(): ?Firm
    {
        return $this->firm;
    }

    /**
     * @return Contact|null
     */
    public function getFirmContact(): ?Contact
    {
        return $this->firm_contact;
    }

    /**
     * @return Beznal|null
     */
    public function getFirmBeznal(): ?Beznal
    {
        return $this->firm_beznal;
    }

    /**
     * @return SchetGood[]|ArrayCollection
     */
    public function getSchetGoods()
    {
        return $this->schet_goods->toArray();
    }

    /**
     * @return string
     */
    public function getPayUrl(): string
    {
        return $this->pay_url;
    }

    public function isUserContactNeed(): bool
    {
        return $this->getFinanceType()->getId() == FinanceType::DEFAULT_BEZNAL_ID;
    }

    public function isPayUrlNeed(): bool
    {
        return $this->getFinanceType()->getId() == FinanceType::DEFAULT_BEZNAL_CARD_ID;
    }

    public function isPayCreditCard(): bool
    {
        return $this->getFinanceType()->getId() == FinanceType::DEFAULT_BEZNAL_CARD_ID;
    }

    public function isPayAllow(): bool
    {
        return $this->getStatus() == self::NOT_PAID;
    }

    public function isCancelAllow(): bool
    {
        return $this->getStatus() == self::NOT_PAID;
    }

    public function isPaid(): bool
    {
        return $this->status === self::PAID;
    }

    public function isNotPaid(): bool
    {
        return $this->status === self::NOT_PAID;
    }

    public function isNew(): bool
    {
        return $this->status === self::NEW;
    }

    public function isCanceled(): bool
    {
        return $this->status === self::CANCELED;
    }

    /**
     * @return SchetGood[]|ArrayCollection
     */
    public function getSumSchetGoods(): float
    {
        $sum = 0;
        foreach ($this->getSchetGoods() as $schetGood) {
            $sum += $schetGood->getPrice() * $schetGood->getQuantity();
        }
        return $sum;
    }

    /**
     * @return OrderGood[]|ArrayCollection
     */
    public function getOrderGoods()
    {
        return $this->order_goods->toArray();
    }

    public function assignOrderGood(OrderGood $orderGood)
    {
        $this->order_goods->add($orderGood);
    }

    public function attachGoodsFromOrderGoods(): void
    {
        foreach ($this->order_goods as $orderGood) {
            $this->attachGood($orderGood, $orderGood->getNumber(), $orderGood->getCreater(), $orderGood->getQuantity(), $orderGood->getDiscountPrice());
        }
    }

    public function attachGood(OrderGood $order_good, DetailNumber $number, Creater $creater, int $quantity, string $price): void
    {
        $isExist = false;
        foreach ($this->schet_goods as $existing) {
            if ($existing->getOrderGood()->getId() == $order_good->getId()) {
                $isExist = true;
            }
        }
        if (!$isExist) {
            $this->schet_goods->add(new SchetGood($this, $order_good, $number, $creater, $quantity, $price));
        }
    }

    public function clearOrderGoods()
    {
        foreach ($this->order_goods as $order_good) {
            $order_good->removeSchet();
        }
    }

    public function getOsnName(): string
    {
        return '№' . $this->getDocument()->getSchetNum() . ' от ' . $this->getDateofadded()->format('d.m.Y');
    }
}
