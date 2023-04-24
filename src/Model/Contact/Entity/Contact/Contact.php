<?php

namespace App\Model\Contact\Entity\Contact;

use App\Model\Contact\Entity\Town\Town;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\Entity\Schet\Schet;
use App\Model\Income\Entity\Document\IncomeDocument;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Order\Order;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ContactRepository::class)
 * @ORM\Table(name="contacts")
 */
class Contact
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="contactID")
     */
    private $contactID;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="contacts")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=true)
     */
    private $manager;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="contacts")
     * @ORM\JoinColumn(name="firmID", referencedColumnName="firmID", nullable=true)
     */
    private $firm;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="contacts")
     * @ORM\JoinColumn(name="userID", referencedColumnName="userID", nullable=true)
     */
    private $user;

    /**
     * @var Town
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Town\Town", inversedBy="contacts")
     * @ORM\JoinColumn(name="townID", referencedColumnName="townID", nullable=false)
     */
    private $town;

    /**
     * @var Address
     * @ORM\Embedded(class="Address", columnPrefix=false)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $phone;

    /**
     * @ORM\Column(type="contact_phonemob", length=30)
     */
    private $phonemob;

    /**
     * @ORM\Column(type="boolean", name="isSMS")
     */
    private $isSMS = 0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fax;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $http;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean", name="isEmail")
     */
    private $isEmail = 0;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $icq = '';

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="boolean", name="isUr")
     */
    private $isUr = 0;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = 0;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = 0;

    /**
     * @var User[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\User\User", mappedBy="cash_user_contact")
     */
    private $cash_users;

    /**
     * @var User[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\User\User", mappedBy="gruz_user_contact")
     */
    private $gruz_users;

    /**
     * @var IncomeDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Document\IncomeDocument", mappedBy="user_contact")
     */
    private $userIncomeDocuments;

    /**
     * @var IncomeDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Income\Entity\Document\IncomeDocument", mappedBy="firm_contact_from")
     */
    private $firmFromIncomeDocuments;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="exp_user_contact")
     */
    private $expenseDocumentsExpUser;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="gruz_user_contact")
     */
    private $expenseDocumentsGruzUser;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="cash_user_contact")
     */
    private $expenseDocumentsCashUser;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="exp_firm_contact")
     */
    private $expenseDocumentsExpFirm;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="gruz_firm_contact")
     */
    private $expenseDocumentsGruzFirm;

    /**
     * @var Order[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Order\Order", mappedBy="user_contact")
     */
    private $orders;

    /**
     * @var Schet[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\Schet\Schet", mappedBy="exp_user_contact")
     */
    private $schetsExpUser;

    /**
     * @var Schet[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\Schet\Schet", mappedBy="firm_contact")
     */
    private $schetsFirm;

    public function __construct(?object $object, Town $town, Address $address, ?string $phone, ?string $phonemob, ?string $fax, ?string $http, ?string $email, ?string $description, bool $isUr, bool $isMain)
    {
        $this->address = $address;
        $this->town = $town;
        $this->phone = $phone ?: '';
        $this->phonemob = $phonemob ?: '';
        $this->fax = $fax ?: '';
        $this->http = $http ?: '';
        $this->email = $email ?: '';
        $this->description = $description ?: '';
        $this->isUr = $isUr;
        $this->isMain = $isMain;
        if ($object instanceof Manager) $this->manager = $object;
        if ($object instanceof User) $this->user = $object;
        if ($object instanceof Firm) $this->firm = $object;
    }

    public function update(Town $town, Address $address, ?string $phone, ?string $phonemob, ?string $fax, ?string $http, ?string $email, ?string $description, bool $isUr, bool $isMain)
    {
        $this->address = $address;
        $this->town = $town;
        $this->phone = $phone ?: '';
        $this->phonemob = $phonemob ?: '';
        $this->fax = $fax ?: '';
        $this->http = $http ?: '';
        $this->email = $email ?: '';
        $this->description = $description ?: '';
        $this->isUr = $isUr;
        $this->isMain = $isMain;
    }

    public function clearMain()
    {
        $this->isMain = false;
    }

    public function getId(): ?int
    {
        return $this->contactID;
    }

    public function getTown(): Town
    {
        return $this->town;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function setManager(Manager $manager): void
    {
        $this->manager = $manager;
    }

    public function getFirm(): ?Firm
    {
        return $this->firm;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }


    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getPhonemob(): ?string
    {
        return $this->phonemob;
    }

    public function getIsSMS(): ?bool
    {
        return $this->isSMS;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function getHttp(): ?string
    {
        return $this->http;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getIsEmail(): ?bool
    {
        return $this->isEmail;
    }

    public function getIcq(): ?string
    {
        return $this->icq;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getIsUr(): ?bool
    {
        return $this->isUr;
    }

    public function getIsHide(): ?bool
    {
        return $this->isHide;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
    }

    public function hide()
    {
        $this->isHide = true;
    }

    public function unHide()
    {
        $this->isHide = false;
    }

    public function getFullAddress(): string
    {
        return
            $this->town->getRegion()->getCountry()->getName() . ", " .
            ($this->town->getName() != $this->town->getRegion()->getName() ? $this->town->getRegion()->getName() . ', ' : '') .
            $this->town->getName() .
            $this->address->getFullAddress();
    }

    public function getFullAddressWithZip(): string
    {
        return
            ($this->address->getZip() ? $this->address->getZip() . ', ' : '') .
            $this->town->getRegion()->getCountry()->getName() . ", " .
            ($this->town->getName() != $this->town->getRegion()->getName() ? $this->town->getRegion()->getName() . ', ' : '') .
            $this->town->getName() .
            $this->address->getFullAddress();
    }

    public function getContactPhones(): string
    {
        $arPhones = [];
        if ($this->phonemob != "") {
            $arPhones[] = 'тел.: ' . $this->phonemob;
            if ($this->phone != "") {
                $arPhones[] = $this->phone;
            }
        } else if ($this->phone != "") {
            $arPhones[] = 'тел.: ' . $this->phone;
        }

        if ($this->fax != "") {
            $arPhones[] = 'факс.: ' . $this->fax;
        }
        return implode(', ', $arPhones);
    }

    public function getFullAddressWithPhones(): string
    {
        return $this->getFullAddress() . ($this->getContactPhones() != '' ? ', ' . $this->getContactPhones() : '');
    }

    public function getFullAddressWithZipAndPhones(): string
    {
        return $this->getFullAddressWithZip() . ($this->getContactPhones() != '' ? ', ' . $this->getContactPhones() : '');
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
            $cash_user->clearCashUserContact();
        }
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
            $gruz_user->clearGruzUserContact();
        }
    }
}
