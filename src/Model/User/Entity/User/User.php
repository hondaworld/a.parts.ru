<?php

namespace App\Model\User\Entity\User;

use App\Model\Auto\Entity\Auto\Auto;
use App\Model\Auto\Entity\Moto\Moto;
use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Contact\Entity\Town\Town;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Document\Entity\Document\Document;
use App\Model\Document\Entity\Identification\DocumentIdentification;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\Shipping\Shipping;
use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\Entity\Schet\Schet;
use App\Model\Income\Entity\Document\IncomeDocument;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\ManagerOperation\ManagerOrderOperation;
use App\Model\Order\Entity\Order\Order;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\Ticket\Entity\ClientTicket\ClientTicket;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
use App\Model\User\Entity\Comment\UserComment;
use App\Model\User\Entity\EmailStatus\UserEmailStatus;
use App\Model\User\Entity\FirmContr\FirmContr;
use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\ShopPayType\ShopPayType;
use App\Model\User\Entity\Sms\UserSms;
use App\Model\User\Service\PhoneMobileHelper;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use function Sodium\add;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="users")
 */
class User
{
    public const EMAIL_SEND_SKLADS =
        [
            0 => 'Все',
            1 => 'МСК ЦС',
            5 => 'СПБ ЦС',
        ];

    public const API_TYPES =
        [
            0 => 'Нет',
            1 => 'Да, без замен',
            2 => 'Да, с заменами',
        ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="userID")
     */
    private $userID;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="users", fetch="EAGER")
     * @ORM\JoinColumn(name="ownerManagerID", referencedColumnName="managerID", nullable=true)
     */
    private $owner;

    /**
     * @var ShopPayType
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\ShopPayType\ShopPayType", fetch="EAGER")
     * @ORM\JoinColumn(name="shop_pay_typeID", referencedColumnName="shop_pay_typeID", nullable=true)
     */
    private $pay_type;

    /**
     * @var Opt
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\Opt\Opt", inversedBy="users")
     * @ORM\JoinColumn(name="optID", referencedColumnName="optID", nullable=true)
     */
    private $opt;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $balance = 0;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2, name="balanceLimit")
     */
    private $balanceLimit = 0;

    /**
     * @ORM\Column(type="user_phonemob", length=30)
     */
    private $phonemob;

    /**
     * @ORM\Column(type="boolean", name="isSMS")
     */
    private $isSMS = true;

    /**
     * @ORM\Column(type="user_phonemob", length=30)
     */
    private $phonemob_new = '';

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $password = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var Name
     * @ORM\Embedded(class="Name", columnPrefix=false)
     */
    private $user_name;

    /**
     * @var Email
     * @ORM\Embedded(class="Email", columnPrefix=false)
     */
    private $email;

    /**
     * @var EmailPrice
     * @ORM\Embedded(class="EmailPrice", columnPrefix=false)
     */
    private $email_price;

    /**
     * @var Debt
     * @ORM\Embedded(class="Debt", columnPrefix=false)
     */
    private $debt;

    /**
     * @var Review
     * @ORM\Embedded(class="Review", columnPrefix=false)
     */
    private $review;

    /**
     * @var Town
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Town\Town", inversedBy="users")
     * @ORM\JoinColumn(name="townID", referencedColumnName="townID", nullable=true)
     */
    private $town;

    /**
     * @ORM\Column(type="string", length=1, name="sex", nullable=false)
     */
    private $sex = "";

    /**
     * @var DateTime
     * @ORM\Column(type="date", name="dateofuser", nullable=true)
     */
    private $dateofuser;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="isHide", nullable=false)
     */
    private $isHide = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $active = true;

    /**
     * @var Ur
     * @ORM\Embedded(class="Ur", columnPrefix=false)
     */
    private $ur;

    /**
     * @var Price
     * @ORM\Embedded(class="Price", columnPrefix="price_")
     */
    private $price;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $is_not_update_discount = false;

    /**
     * @var Creater
     * @ORM\ManyToOne(targetEntity="App\Model\Detail\Entity\Creater\Creater")
     * @ORM\JoinColumn(name="price_createrID", referencedColumnName="createrID", nullable=true)
     */
    private $price_creater;

    /**
     * @var ZapSklad
     * @ORM\ManyToOne(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad")
     * @ORM\JoinColumn(name="price_zapSkladID", referencedColumnName="zapSkladID", nullable=true)
     */
    private $price_sklad;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2, name="discountParts")
     */
    private $discountParts = 0;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2, name="discountService")
     */
    private $discountService = 0;

    /**
     * @ORM\Column(type="integer", name="schetDays")
     */
    private $schetDays = 0;

    /**
     * @ORM\Column(type="integer", name="apiType")
     */
    private $apiType = 0;

    /**
     * @var DateTime
     * @ORM\Column(type="date", name="dateofservice", nullable=true)
     */
    private $dateofservice;

    /**
     * @var DateTime
     * @ORM\Column(type="date", name="dateofdelivery", nullable=true)
     */
    private $dateofdelivery;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $edo = '';

    /**
     * @var Provider[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Provider\Entity\Provider\Provider", inversedBy="exclude_provider_users")
     * @ORM\JoinTable(name="userProviderExclude",
     *      joinColumns={@ORM\JoinColumn(name="userID", referencedColumnName="userID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="providerID", referencedColumnName="providerID")}
     * )
     */
    private $exclude_providers;

    /**
     * @var ProviderPrice[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Provider\Entity\Price\ProviderPrice", inversedBy="show_hide_price_users")
     * @ORM\JoinTable(name="userProviderPriceShow",
     *      joinColumns={@ORM\JoinColumn(name="userID", referencedColumnName="userID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="providerPriceID", referencedColumnName="providerPriceID")}
     * )
     */
    private $show_hide_prices;

    /**
     * @var Contact[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Contact\Entity\Contact\Contact", cascade={"persist"}, mappedBy="user", orphanRemoval=true)
     */
    private $contacts;

    /**
     * @var Beznal[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Beznal\Entity\Beznal\Beznal", cascade={"persist"}, mappedBy="user", orphanRemoval=true)
     */
    private $beznals;

    /**
     * @var Document[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Document\Entity\Document\Document", cascade={"persist"}, mappedBy="user", orphanRemoval=true)
     */
    private $documents;

    /**
     * @var UserEmailStatus[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\User\Entity\EmailStatus\UserEmailStatus", inversedBy="users")
     * @ORM\JoinTable(name="userEmailStatusExclude",
     *      joinColumns={@ORM\JoinColumn(name="userID", referencedColumnName="userID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="userEmailStatusID", referencedColumnName="userEmailStatusID")}
     * )
     */
    private $exclude_email_statuses;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="cash_users")
     * @ORM\JoinColumn(name="cash_userID", referencedColumnName="userID", nullable=true)
     */
    private $cash_user;

    /**
     * @var User[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="User", mappedBy="cash_user")
     */
    private $cash_users;

    /**
     * @var Contact
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Contact\Contact", inversedBy="cash_users")
     * @ORM\JoinColumn(name="cash_user_contactID ", referencedColumnName="contactID", nullable=true)
     */
    private $cash_user_contact;

    /**
     * @var Beznal
     * @ORM\ManyToOne(targetEntity="App\Model\Beznal\Entity\Beznal\Beznal", inversedBy="cash_users")
     * @ORM\JoinColumn(name="cash_user_beznalID  ", referencedColumnName="beznalID", nullable=true)
     */
    private $cash_user_beznal;

    /**
     * @var FirmContr
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\FirmContr\FirmContr", inversedBy="cash_users")
     * @ORM\JoinColumn(name="cash_firmcontrID", referencedColumnName="firmcontrID", nullable=true)
     */
    private $cash_firmcontr;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="isGruzInnKpp")
     */
    private $isGruzInnKpp = false;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="User", inversedBy="gruz_users")
     * @ORM\JoinColumn(name="gruz_userID", referencedColumnName="userID", nullable=true)
     */
    private $gruz_user;

    /**
     * @var User[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="User", mappedBy="gruz_user")
     */
    private $gruz_users;

    /**
     * @var Contact
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Contact\Contact", inversedBy="gruz_users")
     * @ORM\JoinColumn(name="gruz_user_contactID", referencedColumnName="contactID", nullable=true)
     */
    private $gruz_user_contact;

    /**
     * @var Beznal
     * @ORM\ManyToOne(targetEntity="App\Model\Beznal\Entity\Beznal\Beznal", inversedBy="gruz_users")
     * @ORM\JoinColumn(name="gruz_user_beznalID", referencedColumnName="beznalID", nullable=true)
     */
    private $gruz_user_beznal;

    /**
     * @var FirmContr
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\FirmContr\FirmContr", inversedBy="gruz_users")
     * @ORM\JoinColumn(name="gruz_firmcontrID", referencedColumnName="firmcontrID", nullable=true)
     */
    private $gruz_firmcontr;

    /**
     * @var UserBalanceHistory[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\BalanceHistory\UserBalanceHistory", cascade={"persist"}, mappedBy="user", orphanRemoval=true)
     */
    private $balance_history;

    /**
     * @var FirmBalanceHistory[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory", cascade={"persist"}, mappedBy="user", orphanRemoval=true)
     */
    private $firm_balance_history;

    /**
     * @var Auto[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Auto\Entity\Auto\Auto", mappedBy="users")
     */
    private $autos;

    /**
     * @var Moto[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Auto\Entity\Moto\Moto", mappedBy="users")
     */
    private $motos;

    /**
     * @var IncomeDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Document\IncomeDocument", mappedBy="user")
     */
    private $incomeDocuments;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="user")
     */
    private $expenseDocuments;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="exp_user")
     */
    private $expenseDocumentsExp;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="gruz_user")
     */
    private $expenseDocumentsGruz;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="cash_user")
     */
    private $expenseDocumentsCash;

    /**
     * @var Order[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Order\Order", mappedBy="user")
     */
    private $orders;

    /**
     * @var Schet[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\Schet\Schet", mappedBy="user")
     */
    private $schets;

    /**
     * @var Schet[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\Schet\Schet", mappedBy="exp_user")
     */
    private $schetsExp;

    /**
     * @var UserSms[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\Sms\UserSms", cascade={"persist"}, mappedBy="user", orphanRemoval=true)
     */
    private $user_sms;

    /**
     * @var UserComment[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\Comment\UserComment", cascade={"persist"}, mappedBy="user", orphanRemoval=true)
     */
    private $user_comments;

    /**
     * @var Shipping[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Shipping\Shipping", mappedBy="user")
     */
    private $shippings;

    /**
     * @var ManagerOrderOperation[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\ManagerOperation\ManagerOrderOperation", mappedBy="user")
     */
    private $manager_order_operations;

    /**
     * @var ClientTicket[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Ticket\Entity\ClientTicket\ClientTicket", mappedBy="user")
     */
    private $client_tickets;

    public function __construct(
        Opt    $opt,
        string $phonemob,
        Name   $user_name,
        string $name,
        ?Town  $town
    )
    {
        $this->opt = $opt;
        $this->phonemob = $phonemob;
        $this->user_name = $user_name;
        $this->name = $name;
        $this->town = $town;
        $this->email = new Email('', false, false);
        $this->email_price = new EmailPrice('', 0, false, false);
        $this->ur = new Ur();
        $this->debt = new Debt();
        $this->review = new Review();
        $this->price = new Price();
        $this->documents = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->beznals = new ArrayCollection();
        $this->user_comments = new ArrayCollection();
        $this->balance_history = new ArrayCollection();
    }

    public function updatePhoneMobile(string $phonemob, ?bool $isSms)
    {
        $this->phonemob = $phonemob;
        $this->isSMS = $isSms;
    }

    public function updateUserName(Name $user_name)
    {
        $this->user_name = $user_name;
    }

    public function updateName(Name $user_name, string $name, ?Town $town)
    {
        $this->user_name = $user_name;
        $this->name = $name;
        $this->town = $town;
    }

    public function updateEmail(Email $email, array $statuses)
    {
        $this->email = $email;
        $this->exclude_email_statuses = new ArrayCollection($statuses);
    }

    public function updateDop(?DateTime $dateofuser, string $sex)
    {
        $this->dateofuser = $dateofuser;
        $this->sex = $sex;
    }

    public function updatePassword(string $password)
    {
        $this->password = $password;
    }

    public function updateOpt(Opt $opt, ?ShopPayType $pay_type)
    {
        $this->opt = $opt;
        $this->pay_type = $pay_type;
    }

    public function updateUr(string $name, Ur $ur)
    {
        $this->name = $name;
        $this->ur = $ur;
    }

    public function updateOwner(?Manager $owner)
    {
        $this->owner = $owner;
    }

    public function updateEmailPrice(EmailPrice $email_price)
    {
        $this->email_price = $email_price;
    }

    public function updateExcludeProvider(array $providers)
    {
        $this->exclude_providers = new ArrayCollection($providers);
    }

    public function updateShowHidePrice(array $providerPrices)
    {
        $this->show_hide_prices = new ArrayCollection($providerPrices);
    }

    public function updateDiscount(?int $schetDays, ?string $discountParts, ?string $discountService)
    {
        $this->schetDays = $schetDays ?: 0;
        $this->discountParts = $discountParts;
        $this->discountService = $discountService;
    }

    public function updateIsNotUpdateDiscount(bool $is_not_update_discount)
    {
        $this->is_not_update_discount = $is_not_update_discount;
    }

    public function updateDebt(?string $balanceLimit, Debt $debt)
    {
        $this->balanceLimit = $balanceLimit;
        $this->debt = $debt;
    }

    public function updateBalanceLimit(float $balanceLimit)
    {
        $this->balanceLimit = $balanceLimit;
    }

    public function updateReview(Review $review)
    {
        $this->review = $review;
    }

    public function updateApiType(int $apiType)
    {
        $this->apiType = $apiType;
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

    public function updatePrice(Price $price, ?Creater $creater, ?ZapSklad $zapSklad)
    {
        $this->price = $price;
        $this->price_creater = $creater;
        $this->price_sklad = $zapSklad;
    }

    public function updateDateOfService(?DateTime $dateofservice)
    {
        $this->dateofservice = $dateofservice;
    }

    public function updateDateOfDelivery(?DateTime $dateofdelivery)
    {
        $this->dateofdelivery = $dateofdelivery;
    }

    public function assignUserSms(?Manager $manager, string $status_code, string $status_text, string $sms_id, string $sender, string $text)
    {
        $this->user_sms->add(new UserSms($manager, $this, $status_code, $status_text, $sms_id, $this->phonemob, $sender, $text));
    }

    public function getId(): ?int
    {
        return $this->userID;
    }

    public function getOwner(): ?Manager
    {
        return $this->owner;
    }

    public function removeOwner()
    {
        $this->owner = null;
    }

    public function getShopPayType(): ?ShopPayType
    {
        return $this->pay_type;
    }

    public function getOpt(): ?Opt
    {
        return $this->opt;
    }

    public function isRetail(): bool
    {
        return $this->opt->getId() == Opt::DEFAULT_OPT_ID;
    }

    public function allowUpdateDiscount(): bool
    {
        return $this->isRetail() && !$this->isNotUpdateDiscount();
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function credit(string $balance, FinanceType $financeType, Manager $manager, Firm $firm, string $description): void
    {
        $balance = floatval(str_replace(',', '.', $balance));
        $this->changeBalance($balance);
        $this->assignBalanceHistory($balance, $financeType, $manager, $firm, null, null, $description);
    }

    public function creditBySchet(string $balance, FinanceType $financeType, Manager $manager, string $description, ?Schet $schet): void
    {
        $balance = floatval(str_replace(',', '.', $balance));
        $this->changeBalance($balance);
        $this->assignBalanceHistory($balance, $financeType, $manager, $financeType->getFirm(), $schet, null, $description);
    }

    public function debitByExpense(string $balance, string $document_num, ExpenseDocument $expenseDocument, Manager $manager): void
    {
        $balance = floatval(str_replace(',', '.', $balance));
        $this->changeBalance(-$balance);
        $this->assignBalanceHistory(-$balance, $expenseDocument->getFinanceType(), $manager, $expenseDocument->getFirm(), null, $expenseDocument, "Списание по документу " . $document_num . " от " . ($expenseDocument->getDateofadded()->format('d.m.Y')));
    }

    public function assignBalanceHistory(string $balance, FinanceType $finance_type, Manager $manager, Firm $firm, ?Schet $schet, ?ExpenseDocument $expenseDocument, string $description)
    {
        $this->balance_history->add(new UserBalanceHistory($this, $balance, $finance_type, $manager, $firm, $schet, $expenseDocument, $description));
    }

    public function changeBalance(float $sum): void
    {
        $newBalance = $this->balance + $sum;
        if ($newBalance < 0) {
            if ($this->debt->getDebtsDate() == null) $this->debt->setDebtsDate(new DateTime());
        } else {
            $this->debt->setDebtsDate(null);
        }
        $this->balance = $newBalance;
    }

    public function getBalanceLimit(): ?string
    {
        return $this->balanceLimit;
    }

    public function getPhonemob(): ?string
    {
        return $this->phonemob;
    }

    public function isSms(): bool
    {
        return $this->isSMS;
    }

    public function getPhonemobNew(): ?string
    {
        return $this->phonemob_new;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getNameWithPhoneMobile(): string
    {
        return $this->name . ' - ' . $this->phonemob;
    }

    public function getFullNameWithPhoneMobile(): string
    {
        return $this->user_name->getPassportname() . ', ' . (new PhoneMobileHelper($this->phonemob))->getValue();
    }

    public function getFullNameWithPhoneMobileAndOrganization(): string
    {
        return $this->user_name->getPassportname() . ' - ' . (new PhoneMobileHelper($this->phonemob))->getValue() . ($this->ur->getOrganization() ? ' (' . $this->ur->getOrganization() . ')' : '');
    }

    public function getUserName(): Name
    {
        return $this->user_name;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getEmailPrice(): EmailPrice
    {
        return $this->email_price;
    }

    public function getTown(): ?Town
    {
        return $this->town;
    }

    public function getUr(): Ur
    {
        return $this->ur;
    }

    public function getSex(): string
    {
        return $this->sex;
    }

    public function getDateofuser(): ?DateTime
    {
        if ($this->dateofuser && $this->dateofuser->format('Y') == '-0001') return null;
        return $this->dateofuser;
    }

    public function isHide(): bool
    {
        return $this->isHide;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function activate(): void
    {
        $this->active = true;
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
     * @return bool
     */
    public function isNotUpdateDiscount(): bool
    {
        return $this->is_not_update_discount;
    }

    public function generateName(): string
    {
        if ($this->ur->isUr() && $this->ur->getOrganization())
            return $this->ur->getOrganization();

        return $this->getUserName()->generateName();
    }

    public function getFullNameOrOrganization(bool $isAddPersonName = false): string
    {
        if ($this->ur->isUr() && $this->ur->getOrganization())
            return $this->ur->getOrganization();

        return ($isAddPersonName ? 'Частное лицо ' : '') . $this->getUserName()->getFullname();
    }

    public function getPassportNameOrOrganization(bool $isAddPersonName = false): string
    {
        if ($this->ur->isUr() && $this->ur->getOrganization())
            return $this->ur->getOrganization();

        return ($isAddPersonName ? 'Частное лицо ' : '') . $this->getUserName()->getPassportname();
    }

    public function getPassportNameOrOrganizationWithInnKppAndPhonemob(bool $isAddPersonName = false): string
    {
        if ($this->ur->isUr() && $this->ur->getOrganization())
            return $this->ur->getOrganizationWithInnAndKpp();

        return ($isAddPersonName ? 'Частное лицо ' : '') . $this->getFullNameWithPhoneMobile();
    }

    public function getPassportNameOrOrganizationWithPassport(bool $isAddPersonName = false): string
    {
        if ($this->ur->isUr() && $this->ur->getOrganization())
            return $this->ur->getOrganizationWithInnAndKpp();

        $passport = '';
        foreach ($this->getDocuments() as $document) {
            if ($document->getIdentification()->getId() == DocumentIdentification::PASSPORT_ID) {
                $passport = ', Паспорт: ' . $document->getSerial() . " №" . $document->getNumber();
            }
        }

        return ($isAddPersonName ? 'Частное лицо ' : '') . $this->getUserName()->getPassportname() . $passport;
    }

    /**
     * @return Provider[]|ArrayCollection
     */
    public function getExcludeProviders()
    {
        return $this->exclude_providers->toArray();
    }

    /**
     * @return array
     */
    public function getExcludeProviderNames(): array
    {
        $names = [];
        foreach ($this->exclude_providers as $provider) {
            $names[] = $provider->getName();
        }
        return $names;
    }

    /**
     * @return ProviderPrice[]|ArrayCollection
     */
    public function getShowHidePrices()
    {
        return $this->show_hide_prices->toArray();
    }

    /**
     * @return array
     */
    public function getShowHidePriceNames(): array
    {
        $names = [];
        foreach ($this->show_hide_prices as $providerPrice) {
            $names[] = $providerPrice->getDescription();
        }
        return $names;
    }

    /**
     * @return float
     */
    public function getDiscountParts(): float
    {
        return $this->discountParts;
    }

    /**
     * @return float
     */
    public function getDiscountService(): float
    {
        return $this->discountService;
    }

    /**
     * @return int
     */
    public function getSchetDays(): int
    {
        return $this->schetDays;
    }

    /**
     * @return Debt
     */
    public function getDebt(): Debt
    {
        return $this->debt;
    }

    /**
     * @return Review
     */
    public function getReview(): Review
    {
        return $this->review;
    }

    /**
     * @return UserEmailStatus[]|array
     */
    public function getExcludeEmailStatuses(): array
    {
        return $this->exclude_email_statuses->toArray();
    }

    public function getExcludeEmailStatusIds(): array
    {
        return array_map(function (UserEmailStatus $userEmailStatus): int {
            return $userEmailStatus->getId();
        }, $this->exclude_email_statuses->toArray());
    }

    public function isAllowSendEmailWithDocuments(): bool
    {
        return !in_array(UserEmailStatus::DOCUMENT_SENT, $this->getExcludeEmailStatusIds());
    }

    public function isAllowSendEmailWithIncomeStatuses(): bool
    {
        return !in_array(UserEmailStatus::CHANGE_INCOME_STATUS, $this->getExcludeEmailStatusIds());
    }

    /**
     * @return int
     */
    public function getApiType(): int
    {
        return $this->apiType;
    }

    /**
     * @return User
     */
    public function getCashUser(): ?User
    {
        return $this->cash_user;
    }

    /**
     * @return User[]|array
     */
    public function getCashUsers(): array
    {
        return $this->cash_users->toArray();
    }

    public function clearCashUsers()
    {
        foreach ($this->cash_users as $cash_user) {
            $cash_user->cash_user = null;
        }
    }

    /**
     * @return Contact
     */
    public function getCashUserContact(): ?Contact
    {
        return $this->cash_user_contact;
    }

    public function clearCashUserContact()
    {
        $this->cash_user_contact = null;
    }

    /**
     * @return Beznal
     */
    public function getCashUserBeznal(): ?Beznal
    {
        return $this->cash_user_beznal;
    }

    public function clearCashUserBeznal()
    {
        $this->cash_user_beznal = null;
    }

    /**
     * @return FirmContr
     */
    public function getCashFirmContr(): ?FirmContr
    {
        return $this->cash_firmcontr;
    }

    public function clearCashFirmContr()
    {
        $this->cash_firmcontr = null;
    }

    /**
     * @return bool
     */
    public function isGruzInnKpp(): bool
    {
        return $this->isGruzInnKpp;
    }

    /**
     * @return User
     */
    public function getGruzUser(): ?User
    {
        return $this->gruz_user;
    }

    /**
     * @return User[]|array
     */
    public function getGruzUsers(): array
    {
        return $this->gruz_users->toArray();
    }

    public function clearGruzUsers()
    {
        foreach ($this->gruz_users as $gruz_user) {
            $gruz_user->gruz_user = null;
        }
    }

    /**
     * @return Contact
     */
    public function getGruzUserContact(): ?Contact
    {
        return $this->gruz_user_contact;
    }

    public function clearGruzUserContact()
    {
        $this->gruz_user_contact = null;
    }

    /**
     * @return Beznal
     */
    public function getGruzUserBeznal(): ?Beznal
    {
        return $this->gruz_user_beznal;
    }

    public function clearGruzUserBeznal()
    {
        $this->gruz_user_beznal = null;
    }

    /**
     * @return FirmContr
     */
    public function getGruzFirmContr(): ?FirmContr
    {
        return $this->gruz_firmcontr;
    }

    public function clearGruzFirmContr()
    {
        $this->gruz_firmcontr = null;
    }

    /**
     * @return Price
     */
    public function getPrice(): Price
    {
        return $this->price;
    }

    /**
     * @return Creater
     */
    public function getPriceCreater(): ?Creater
    {
        return $this->price_creater;
    }

    /**
     * @return ZapSklad
     */
    public function getPriceSklad(): ?ZapSklad
    {
        return $this->price_sklad;
    }

    /**
     * @return UserBalanceHistory[]|array
     */
    public function getBalanceHistory(): array
    {
        return $this->balance_history->toArray();
    }

    /**
     * @return Auto[]|ArrayCollection
     */
    public function getAutos()
    {
        return $this->autos->toArray();
    }

    /**
     * @return Moto[]|ArrayCollection
     */
    public function getMotos()
    {
        return $this->motos->toArray();
    }

    /**
     * @return Order[]|ArrayCollection
     */
    public function getOrders()
    {
        return $this->orders->toArray();
    }

    /**
     * @return Order|null
     */
    public function getLastOrder(): ?Order
    {
        $order = null;
        $orders = array_filter($this->getOrders(), function (Order $a) {
            return $a->getStatus() == Order::ORDER_STATUS_WORK;
        });
        if ($orders) {
            usort($orders, function (Order $a, Order $b) {
                return $b->getDateofadded()->getTimestamp() <=> $a->getDateofadded()->getTimestamp();
            });
            $order = $orders[0];
        }
        return $order;
    }

    /**
     * @return DateTime|null
     */
    public function getDateofservice(): ?DateTime
    {
        return $this->dateofservice;
    }

    /**
     * @return DateTime|null
     */
    public function getDateofdelivery(): ?DateTime
    {
        return $this->dateofdelivery;
    }

    /**
     * @return bool
     */
    public function isDebt(): bool
    {
        if (!$this->getDebt()->getDebtsDate()) return false;
        if ((new \DateTime())->diff($this->getDebt()->getDebtsDate())->days > $this->getDebt()->getDebtsDays()) return true;
        return false;
    }

    public function isAllowBalanceForOrder(float $sum): bool
    {
//        if (($row->debts_days == 0) && ($balanceLimit > 0)) $balanceLimit = 0;
//        if ($row->balance - $summ_goods + $balanceLimit < 0) {
//
//        }
        if ($this->getDebt()->getDebtsDays() == 0) $balanceLimit = 0; else $balanceLimit = $this->balanceLimit;

        return $this->balance - $sum + $balanceLimit >= 0;
    }

    /**
     * @return string
     */
    public function getEdo(): string
    {
        return $this->edo;
    }

    public function getUserEmailPrice(): string
    {
        return $this->getEmailPrice()->getValue() != '' ? $this->getEmailPrice()->getValue() : $this->getEmail()->getValue();
    }

    public function getUserPriceEmail(): string
    {
        return $this->getEmail()->getValue() != '' ? $this->getEmail()->getValue() : $this->getPrice()->getEmail();
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

    public function assignUserComment(Manager $manager, string $comment): void
    {
        $this->user_comments->add(new UserComment($manager, $this, $comment));
    }

    /**
     * @return UserComment[]|ArrayCollection
     */
    public function getUserComments()
    {
        return $this->user_comments;
    }
}
