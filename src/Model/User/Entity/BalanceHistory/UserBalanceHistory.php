<?php

namespace App\Model\User\Entity\BalanceHistory;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\Entity\Schet\Schet;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Check\Check;
use App\Model\User\Entity\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UserBalanceHistoryRepository::class)
 * @ORM\Table(name="userBalanceHistory")
 */
class UserBalanceHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="balanceID")
     */
    private $id;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="balance_history")
     * @ORM\JoinColumn(name="userID", referencedColumnName="userID", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $balance;

    /**
     * @var FinanceType
     * @ORM\ManyToOne(targetEntity="App\Model\Finance\Entity\FinanceType\FinanceType", inversedBy="balance_history")
     * @ORM\JoinColumn(name="finance_typeID", referencedColumnName="finance_typeID", nullable=false)
     */
    private $finance_type;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="balance_history")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=true)
     */
    private $manager;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="balance_history")
     * @ORM\JoinColumn(name="firmID", referencedColumnName="firmID", nullable=true)
     */
    private $firm;

    /**
     * @var Schet
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Schet\Schet", inversedBy="balance_history")
     * @ORM\JoinColumn(name="schetID", referencedColumnName="schetID", nullable=true)
     */
    private $schet;

    /**
     * @var ExpenseDocument
     * @ORM\ManyToOne(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", inversedBy="balance_history")
     * @ORM\JoinColumn(name="expenseDocumentID", referencedColumnName="expenseDocumentID", nullable=true)
     */
    private $expenseDocument;

    /**
     * @ORM\Column(type="text")
     */
    private $description = '';

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $attach = '';

    /**
     * @var Check
     * @ORM\OneToOne(targetEntity="App\Model\Order\Entity\Check\Check", mappedBy="balance")
     */
    private $check;

    public function __construct(User $user, string $balance, FinanceType $finance_type, Manager $manager, Firm $firm, ?Schet $schet, ?ExpenseDocument $expenseDocument, string $description)
    {
        $this->user = $user;
        $this->balance = $balance;
        $this->finance_type = $finance_type;
        $this->manager = $manager;
        $this->firm = $firm;
        $this->schet = $schet;
        $this->expenseDocument = $expenseDocument;
        $this->description = $description;
        $this->dateofadded = new \DateTime();
    }

    public function update(string $balance, Firm $firm, string $description)
    {
        $balance = floatval(str_replace(',', '.', $balance));
        $this->user->changeBalance($balance - $this->balance);

        $this->balance = $balance;
        $this->firm = $firm;
        $this->description = $description;
    }

    public function updateFinanceType(Firm $firm, FinanceType $financeType)
    {
        $this->firm = $firm;
        $this->finance_type = $financeType;
    }

    public function updateAttach(string $attach)
    {
        $this->attach = $attach;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function getFinanceType(): FinanceType
    {
        return $this->finance_type;
    }

    public function getManager(): ?Manager
    {
        return $this->manager;
    }

    public function getFirm(): Firm
    {
        return $this->firm;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getAttach(): ?string
    {
        return $this->attach;
    }

    public function removeManager(): void
    {
        $this->manager = null;
    }

    public function removeAttach(): void
    {
        $this->attach = '';
    }

    /**
     * @return Schet
     */
    public function getSchet(): ?Schet
    {
        return $this->schet;
    }

    /**
     * @return ExpenseDocument
     */
    public function getExpenseDocument(): ?ExpenseDocument
    {
        return $this->expenseDocument;
    }

    /**
     * @return Check
     */
    public function getCheck(): ?Check
    {
        return $this->check;
    }


}
