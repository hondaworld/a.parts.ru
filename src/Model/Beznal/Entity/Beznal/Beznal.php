<?php

namespace App\Model\Beznal\Entity\Beznal;

use App\Model\Beznal\Entity\Bank\Bank;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\Entity\Schet\Schet;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Order\Order;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BeznalRepository::class)
 * @ORM\Table(name="beznals")
 */
class Beznal
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="beznalID")
     */
    private $beznalID;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="beznals")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=true)
     */
    private $manager;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="beznals")
     * @ORM\JoinColumn(name="firmID", referencedColumnName="firmID", nullable=true)
     */
    private $firm;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="beznals")
     * @ORM\JoinColumn(name="userID", referencedColumnName="userID", nullable=true)
     */
    private $user;

    /**
     * @var Bank
     * @ORM\ManyToOne(targetEntity="App\Model\Beznal\Entity\Bank\Bank", inversedBy="beznals")
     * @ORM\JoinColumn(name="bankID", referencedColumnName="bankID", nullable=false)
     */
    private $bank;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $rasschet;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="boolean", name="isMain")
     */
    private $isMain = false;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var User[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\User\User", mappedBy="cash_user_beznal")
     */
    private $cash_users;

    /**
     * @var User[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\User\Entity\User\User", mappedBy="gruz_user_beznal")
     */
    private $gruz_users;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="exp_user_beznal")
     */
    private $expenseDocumentsExpUser;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="gruz_user_beznal")
     */
    private $expenseDocumentsGruzUser;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="cash_user_beznal")
     */
    private $expenseDocumentsCashUser;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="exp_firm_beznal")
     */
    private $expenseDocumentsExpFirm;

    /**
     * @var ExpenseDocument[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", mappedBy="gruz_firm_beznal")
     */
    private $expenseDocumentsGruzFirm;

    /**
     * @var Order[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Order\Order", mappedBy="user_beznal")
     */
    private $orders;

    /**
     * @var Schet[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Firm\Entity\Schet\Schet", mappedBy="firm_beznal")
     */
    private $schetsFirm;

    public function __construct(?object $object, Bank $bank, string $rasschet, ?string $description, bool $isMain)
    {
        $this->bank = $bank;
        $this->rasschet = $rasschet;
        $this->description = $description ?: '';
        $this->isMain = $isMain;
        if ($object instanceof Manager) $this->manager = $object;
        if ($object instanceof User) $this->user = $object;
        if ($object instanceof Firm) $this->firm = $object;
    }

    public function update(Bank $bank, string $rasschet, ?string $description, bool $isMain)
    {
        $this->bank = $bank;
        $this->rasschet = $rasschet;
        $this->description = $description ?: '';
        $this->isMain = $isMain;
    }

    public function clearMain()
    {
        $this->isMain = false;
    }

    public function getId(): ?int
    {
        return $this->beznalID;
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

    public function setUserID(?int $userID): self
    {
        $this->userID = $userID;

        return $this;
    }

    public function getBank(): Bank
    {
        return $this->bank;
    }

    public function getRasschet(): ?string
    {
        return $this->rasschet;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isHide(): ?bool
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

    public function getFullRequisite()
    {
        return 'р/с №' . $this->rasschet . ' в ' . $this->bank->getName();
    }

    public function getFullRequisiteWithBik()
    {
        $beznal = $this->getFullRequisite();

        if ($this->bank->getBik() != "") {
            $beznal .= ", БИК " . $this->bank->getBik();
        }
        if ($this->bank->getKorschet() != "") {
            $beznal .= ", к/с  " . $this->bank->getKorschet();
        }
        return $beznal;
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
            $cash_user->clearCashUserBeznal();
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
            $gruz_user->clearGruzUserBeznal();
        }
    }
}
