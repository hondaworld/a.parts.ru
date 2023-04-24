<?php

namespace App\Model\User\Entity\FirmContr;

use App\Model\Beznal\Entity\Bank\Bank;
use App\Model\Contact\Entity\Town\Town;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FirmContrRepository::class)
 * @ORM\Table(name="firmcontr")
 */
class FirmContr
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="firmcontrID")
     */
    private $firmcontrID;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @var Ur
     * @ORM\Embedded(class="Ur", columnPrefix=false)
     */
    private $ur;

    /**
     * @var Town
     * @ORM\ManyToOne(targetEntity="App\Model\Contact\Entity\Town\Town", inversedBy="firmcontr")
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
     * @ORM\Column(type="string", length=255)
     */
    private $fax;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @var Bank
     * @ORM\ManyToOne(targetEntity="App\Model\Beznal\Entity\Bank\Bank", inversedBy="firmcontr")
     * @ORM\JoinColumn(name="bankID", referencedColumnName="bankID", nullable=false)
     */
    private $bank;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $rasschet;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $gendir = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $buhgalter = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $osn = '';

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @var User[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\User\User", mappedBy="cash_firmcontr")
     */
    private $cash_users;

    /**
     * @var User[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\User\User", mappedBy="gruz_firmcontr")
     */
    private $gruz_users;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="gruz_firmcontr")
     */
    private $expenseDocumentsGruz;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="cash_firmcontr")
     */
    private $expenseDocumentsCash;

    public function __construct(Ur $ur, Town $town, Address $address, ?string $phone, ?string $fax, ?string $email, Bank $bank, ?string $rasschet)
    {
        $this->dateofadded = new \DateTime();
        $this->ur = $ur;
        $this->town = $town;
        $this->address = $address;
        $this->phone = $phone ?: '';
        $this->fax = $fax ?: '';
        $this->email = $email ?: '';
        $this->bank = $bank;
        $this->rasschet = $rasschet ?: '';
    }

    public function update(Ur $ur, Town $town, Address $address, ?string $phone, ?string $fax, ?string $email, Bank $bank, ?string $rasschet)
    {
        $this->ur = $ur;
        $this->town = $town;
        $this->address = $address;
        $this->phone = $phone ?: '';
        $this->fax = $fax ?: '';
        $this->email = $email ?: '';
        $this->bank = $bank;
        $this->rasschet = $rasschet ?: '';
    }

    public function getId(): ?int
    {
        return $this->firmcontrID;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    /**
     * @return Ur
     */
    public function getUr(): Ur
    {
        return $this->ur;
    }

    /**
     * @return Town
     */
    public function getTown(): Town
    {
        return $this->town;
    }

    /**
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * @return Bank
     */
    public function getBank(): Bank
    {
        return $this->bank;
    }


    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function getRasschet(): ?string
    {
        return $this->rasschet;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getGendir(): ?string
    {
        return $this->gendir;
    }

    public function getBuhgalter(): ?string
    {
        return $this->buhgalter;
    }

    public function getOsn(): ?string
    {
        return $this->osn;
    }

    public function isHide(): ?bool
    {
        return $this->isHide;
    }

    public function isMain(): ?bool
    {
        return $this->isMain;
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unHide(): void
    {
        $this->isHide = false;
    }

    public function clearCashUsers()
    {
        foreach ($this->cash_users as $cash_user) {
            $cash_user->clearCashFirmContr();
        }
    }

    public function clearGruzUsers()
    {
        foreach ($this->gruz_users as $gruz_user) {
            $gruz_user->clearGruzFirmContr();
        }
    }

    public function getFullAddressWithZipAndPhones(): string
    {
        return $this->getFullAddressWithZip() . ($this->getContactPhones() != '' ? ', ' . $this->getContactPhones() : '');
    }

    public function getFullAddressWithPhones(): string
    {
        return $this->getFullAddress() . ($this->getContactPhones() != '' ? ', ' . $this->getContactPhones() : '');
    }

    public function getFullAddressWithZip(): string
    {
        return
            ($this->address->getZip() ? $this->address->getZip() . ', ' : '') .
            $this->getFullAddress();
    }

    public function getFullAddress(): string
    {
        return
            $this->town->getRegion()->getCountry()->getName() . ", " .
            ($this->town->getName() != $this->town->getRegion()->getName() ? $this->town->getRegion()->getName() . ', ' : '') .
            $this->town->getName() .
            $this->address->getFullAddress();
    }

    public function getContactPhones(): string
    {
        $arPhones = [];
        if ($this->phone != "") {
            $arPhones[] = 'тел.: ' . $this->phone;
        }

        if ($this->fax != "") {
            $arPhones[] = 'факс.: ' . $this->fax;
        }
        return implode(', ', $arPhones);
    }

    public function getFullRequisiteWithBik(): string
    {
        $beznal = $this->getFullRequisite();

        if ($this->bank->getBik() != "") {
            $beznal .= ", БИК " . $this->bank->getBik();
        }
        if ($this->bank->getKorschet() != "") {
            $beznal .= ", к/с " . $this->bank->getKorschet();
        }
        return $beznal;
    }

    public function getFullRequisite(): string
    {
        return 'р/с №' . $this->rasschet . ' в ' . $this->bank->getName();
    }
}
