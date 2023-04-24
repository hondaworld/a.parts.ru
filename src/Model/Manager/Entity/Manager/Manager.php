<?php

namespace App\Model\Manager\Entity\Manager;

use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Reserve\ZapCardReserve;
use App\Model\Card\Entity\ReserveSklad\ZapCardReserveSklad;
use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Detail\Entity\Zamena\ShopZamena;
use App\Model\Document\Entity\Document\Document;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use App\Model\Expense\Entity\SkladDocument\ExpenseSkladDocument;
use App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\Entity\ManagerFirm\ManagerFirm;
use App\Model\Income\Entity\Document\IncomeDocument;
use App\Model\Income\Entity\Good\IncomeGood;
use App\Model\Income\Entity\StatusHistory\IncomeStatusHistory;
use App\Model\Manager\Entity\FavouriteMenu\FavouriteMenu;
use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Manager\Entity\Type\ManagerType;
use App\Model\Order\Entity\Check\Check;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Order\Entity\ManagerOperation\ManagerOrderOperation;
use App\Model\Order\Entity\Order\Order;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\Ticket\Entity\ClientTicket\ClientTicket;
use App\Model\Ticket\Entity\ClientTicketGroup\ClientTicketGroup;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
use App\Model\User\Entity\Comment\UserComment;
use App\Model\User\Entity\Sms\UserSms;
use App\Model\User\Entity\User\User;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToMany;

/**
 * @ORM\Entity(repositoryClass=ManagerRepository::class)
 * @ORM\Table(name="managers")
 */
class Manager
{
    public const PHOTO_MAX_WIDTH = 200;
    public const PHOTO_MAX_HEIGHT = 200;

    public const SUPER_ADMIN = 1;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="managerID")
     */
    private $managerID;

    /**
     * @var string
     * @ORM\Column(type="string", length=180, unique=true, nullable=false)
     */
    private $login;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $name;

    /**
     * @var Name
     * @ORM\Embedded(class="Name", columnPrefix=false)
     */
    private $managerName;

    /**
     * @var string
     * @ORM\Column(type="manager_phonemob", length=30, nullable=false)
     */
    private $phonemob;

    /**
     * @var Email
     * @ORM\Column(type="manager_email", nullable=false)
     */
    private $email = "";

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $photo = "";

    /**
     * @ORM\Column(type="string", length=1, name="sex", nullable=false)
     */
    private $sex = "";

    /**
     * @var string
     * @ORM\Column(type="string", name="dateofmanager", nullable=false)
     */
    private $dateofmanger = '0000-00-00';

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="isHide", nullable=false)
     */
    private $isHide = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="isManager", nullable=false)
     */
    private $isManager = false;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="isAdmin", nullable=false)
     */

    private $isAdmin = false;
    /**
     * ORM\Column(type="json")
     */
//    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=false)
     */
    private $password_admin = "";

    /**
     * @ORM\Column(type="koef", precision=5, scale=2)
     */
    private $zp_spare = 0;

    /**
     * @ORM\Column(type="koef", precision=5, scale=2)
     */
    private $zp_service = 0;

    /**
     * @ORM\Column(type="string", length=3, name="nick", nullable=false)
     */
    private $nick;

    /**
     * @var ManagerGroup[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Manager\Entity\Group\ManagerGroup", inversedBy="managers")
     * @ORM\JoinTable(name="linkManagerGroup",
     *      joinColumns={@ORM\JoinColumn(name="manager_id", referencedColumnName="managerID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="manager_group_id", referencedColumnName="managerGroupID")}
     * )
     * @ORM\OrderBy({"name" = "ASC"})
     */
    private $groups;

    /**
     * @var ZapSklad[]|ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Model\Sklad\Entity\ZapSklad\ZapSklad", inversedBy="managers")
     * @ORM\JoinTable(name="linkReportManagerSklad",
     *      joinColumns={@ORM\JoinColumn(name="managerID", referencedColumnName="managerID")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="zapSkladID", referencedColumnName="zapSkladID")}
     * )
     * @ORM\OrderBy({"name_short" = "ASC"})
     */
    private $sklads;

    /**
     * @var ManagerType
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Type\ManagerType", inversedBy="managers")
     * @ORM\JoinColumn(name="managerTypeID", referencedColumnName="managerTypeID")
     */
    private $type;

    /**
     * @var Contact[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Contact\Entity\Contact\Contact", mappedBy="manager", cascade={"persist"}, orphanRemoval=true)
     */
    private $contacts;

    /**
     * @var Beznal[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Beznal\Entity\Beznal\Beznal", mappedBy="manager", cascade={"persist"}, orphanRemoval=true)
     */
    private $beznals;

    /**
     * @var Document[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Document\Entity\Document\Document", mappedBy="manager", cascade={"persist"}, orphanRemoval=true)
     */
    private $documents;

    /**
     * @var Firm[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\Firm\Firm", mappedBy="director")
     */
    private $director_firms;

    /**
     * @var Firm[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\Firm\Firm", mappedBy="buhgalter")
     */
    private $buhgalter_firms;

    /**
     * @var User[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\User\User", mappedBy="owner")
     */
    private $users;

    /**
     * @var ManagerFirm[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\ManagerFirm\ManagerFirm", mappedBy="manager", orphanRemoval=true)
     */
    private $manager_firms;

    /**
     * @var UserBalanceHistory[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\BalanceHistory\UserBalanceHistory", mappedBy="manager")
     */
    private $balance_history;

    /**
     * @var FirmBalanceHistory[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory", mappedBy="manager")
     */
    private $firm_balance_history;

    /**
     * @var ShopZamena[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Detail\Entity\Zamena\ShopZamena", mappedBy="manager")
     */
    private $zamena;

    /**
     * @var IncomeDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Document\IncomeDocument", mappedBy="manager")
     */
    private $incomeDocuments;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="manager")
     */
    private $expenseDocuments;

    /**
     * @var ExpenseSkladDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\SkladDocument\ExpenseSkladDocument", mappedBy="manager")
     */
    private $expenseSkladDocuments;

    /**
     * @var IncomeStatusHistory[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\StatusHistory\IncomeStatusHistory", mappedBy="manager")
     */
    private $income_status_history;

    /**
     * @var Order[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Order\Order", mappedBy="manager")
     */
    private $orders;

    /**
     * @var OrderGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Good\OrderGood", mappedBy="manager")
     */
    private $order_goods;

    /**
     * @var IncomeGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Good\IncomeGood", mappedBy="manager")
     */
    private $income_goods;

    /**
     * @var ExpenseSklad[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Sklad\ExpenseSklad", mappedBy="manager")
     */
    private $expense_sklads;

    /**
     * @var ZapCardReserve[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Reserve\ZapCardReserve", mappedBy="manager")
     */
    private $zapCardReserve;

    /**
     * @var ZapCardReserveSklad[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\ReserveSklad\ZapCardReserveSklad", mappedBy="manager")
     */
    private $zapCardReserveSklad;

    /**
     * @var UserSms[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\Sms\UserSms", mappedBy="manager")
     */
    private $user_sms;

    /**
     * @var UserComment[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\Comment\UserComment", mappedBy="manager")
     */
    private $user_comments;

    /**
     * @var ManagerOrderOperation[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\ManagerOperation\ManagerOrderOperation", mappedBy="manager", cascade={"persist"})
     */
    private $manager_order_operations;

    /**
     * @var Check[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Check\Check", mappedBy="manager")
     */
    private $checks;

    /**
     * @var FavouriteMenu[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Manager\Entity\FavouriteMenu\FavouriteMenu", mappedBy="manager")
     */
    private $favouriteMenus;

    /**
     * @var ClientTicketGroup[]|ArrayCollection
     * @ManyToMany(targetEntity="App\Model\Ticket\Entity\ClientTicketGroup\ClientTicketGroup", mappedBy="managers")
     */
    private $client_ticket_groups;

    /**
     * @var ClientTicket[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Ticket\Entity\ClientTicket\ClientTicket", mappedBy="manager")
     */
    private $client_tickets;

    /**
     * @var ZapCard[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Card\ZapCard", mappedBy="manager")
     */
    private $zapCards;

    public function __construct(
        string      $login,
        string      $password_admin,
        Name        $managerName,
        string      $name,
        ManagerType $type
    )
    {
        $this->login = $login;
        $this->password_admin = $password_admin;
        $this->name = $name;
        $this->managerName = $managerName;
        $this->nick = $managerName->generateNick();
        $this->groups = new ArrayCollection();
        $this->type = $type;
        $this->documents = new ArrayCollection();
        $this->contacts = new ArrayCollection();
        $this->beznals = new ArrayCollection();
        $this->zapCards = new ArrayCollection();
    }

    public function update(
        string      $login,
        string      $phonemob,
        Name        $managerName,
        string      $name,
        string      $nick,
        Email       $email,
        string      $sex,
        bool        $isHide,
        bool        $isManager,
        bool        $isAdmin,
        DateTime    $dateofmanger,
        string      $photo,
        ManagerType $type,
        ?string     $zp_spare,
        ?string     $zp_service
    ): void
    {
        $this->login = $login;
        $this->name = $name;
        $this->nick = mb_strtoupper($nick);
        $this->phonemob = $phonemob;
        $this->managerName = $managerName;
        $this->email = $email;
        $this->sex = $sex;
        $this->isHide = $isHide;
        $this->isManager = $isManager;
        $this->isAdmin = $isAdmin;
        $this->dateofmanger = $dateofmanger->format('Y-m-d');
        $this->photo = $photo;
        $this->type = $type;
        $this->zp_spare = $zp_spare;
        $this->zp_service = $zp_service;
    }

    public function updateProfile(
        string   $login,
        string   $phonemob,
        Name     $managerName,
        string   $name,
        Email    $email,
        string   $sex,
        DateTime $dateofmanger,
        string   $photo
    ): void
    {
        $this->login = $login;
        $this->name = $name;
        $this->phonemob = $phonemob;
        $this->managerName = $managerName;
        $this->email = $email;
        $this->sex = $sex;
        $this->dateofmanger = $dateofmanger->format('Y-m-d');
        $this->photo = $photo;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getNick(): string
    {
        return $this->nick;
    }

    /**
     * @return string
     */
    public function getPasswordAdmin(): string
    {
        return $this->password_admin;
    }

    /**
     * @param string $password_admin
     */
    public function changePasswordAdmin(string $password_admin): void
    {
        $this->password_admin = $password_admin;
    }

    public function getId(): ?int
    {
        return $this->managerID;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    /**
     * @return bool
     */
    public function getIsHide()
    {
        return $this->isHide;
    }

    /**
     * @return Name
     */
    public function getManagerName(): Name
    {
        return $this->managerName;
    }

    /**
     * @return bool
     */
    public function getIsManager(): bool
    {
        return $this->isManager;
    }

    /**
     * @return bool
     */
    public function getIsAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getDateofmanger(): ?DateTime
    {
        if ($this->dateofmanger == 0) return null;
        return new DateTime($this->dateofmanger);
    }

    /**
     * @return mixed
     */
    public function getPhonemob()
    {
        return $this->phonemob;
    }

    /**
     * @return string
     */
    public function getSex(): string
    {
        return $this->sex;
    }

    /**
     * @return Email
     */
    public function getEmail(): Email
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPhoto(): string
    {
        return $this->photo;
    }

    public function deletePhoto(): void
    {
        $this->photo = '';
    }

    public function getPhotoDirectory(): string
    {
        return 'uploads/manager/';
    }

    public function getType(): ManagerType
    {
        return $this->type;
    }

    /**
     * @return ZapSklad[]|ArrayCollection
     */
    public function getSklads()
    {
        return $this->sklads->toArray();
    }

    public function clearSklads(): void
    {
        $this->sklads->clear();
    }

    /**
     * @param ZapSklad $sklad
     */
    public function assignSklad(ZapSklad $sklad): void
    {
        $this->sklads->add($sklad);
    }

    /**
     * @return ManagerGroup[]|ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups->toArray();
    }

    public function clearGroups(): void
    {
        $this->groups->clear();
    }

    /**
     * @param ManagerGroup $group
     */
    public function assignGroup(ManagerGroup $group): void
    {
        $this->groups->add($group);
    }

    /**
     * @return ManagerFirm[]|ArrayCollection
     */
    public function getManagerFirms()
    {
        return $this->manager_firms->toArray();
    }

    /**
     * @return float
     */
    public function getZpSpare(): float
    {
        return $this->zp_spare;
    }

    /**
     * @return float
     */
    public function getZpService(): float
    {
        return $this->zp_service;
    }

    /**
     * @return Firm[]|ArrayCollection
     */
    public function getDirectorFirms()
    {
        return $this->director_firms;
    }

    public function clearDirectorFirms()
    {
        foreach ($this->director_firms as $director_firm) {
            $director_firm->removeDirector();
        }
    }

    /**
     * @return Firm[]|ArrayCollection
     */
    public function getBuhgalterFirms()
    {
        return $this->buhgalter_firms;
    }

    public function clearBuhgalterFirms()
    {
        foreach ($this->buhgalter_firms as $buhgalter_firm) {
            $buhgalter_firm->removeBuhgalter();
        }
    }

    /**
     * @return User[]|ArrayCollection
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function clearUsers()
    {
        foreach ($this->users as $user) {
            $user->removeOwner();
        }
    }

    /**
     * @return UserBalanceHistory[]|array
     */
    public function getBalanceHistory(): array
    {
        return $this->balance_history->toArray();
    }

    public function clearBalanceHistory(): void
    {
        foreach ($this->balance_history as $balance_history) {
            $balance_history->removeManager();
        }
    }

    public function assignOrderOperation(?User $user, ?Order $order, string $description, string $number = ''): void
    {
        $managerOrderOperation = new ManagerOrderOperation($this, $user, $order, $description, $number);
        $this->manager_order_operations->add($managerOrderOperation);
    }

    public function removeContact(Contact $contact): void
    {
        if ($this->contacts->contains($contact)) {
            $this->contacts->removeElement($contact);
        }
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

    /**
     * @return ZapCard[]|ArrayCollection
     */
    public function getZapCards()
    {
        return $this->zapCards;
    }
}
