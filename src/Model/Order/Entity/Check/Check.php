<?php

namespace App\Model\Order\Entity\Check;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CheckRepository::class)
 * @ORM\Table(name="checks")
 */
class Check
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var ExpenseDocument
     * @ORM\OneToOne(targetEntity="App\Model\Expense\Entity\Document\ExpenseDocument", inversedBy="check")
     * @ORM\JoinColumn(name="expenseDocumentID", referencedColumnName="expenseDocumentID", nullable=true)
     */
    private $expenseDocument;

    /**
     * @var UserBalanceHistory
     * @ORM\OneToOne(targetEntity="App\Model\User\Entity\BalanceHistory\UserBalanceHistory", inversedBy="check")
     * @ORM\JoinColumn(name="balanceID", referencedColumnName="balanceID", nullable=true)
     */
    private $balance;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="checks")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=false)
     */
    private $manager;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $summ;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $check_summ = 0;

    /**
     * @ORM\Column(type="integer")
     */
    private $kassa_id = 0;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $state = '';

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $fiscal_time = '';

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $fiscal_summ = 0;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $fiscal_fabric_number = '';

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $fiscal_doc_number = '';

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $fiscal_doc_type = '';

    /**
     * @ORM\Column(type="integer")
     */
    private $fiscal_type = 0;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $fiscal_smena_number = '';

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $fiscal_check_number = '';

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $fiscal_register_number = '';

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $error_description = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExpenseDocument(): ?ExpenseDocument
    {
        return $this->expenseDocument;
    }

    public function getBalance(): ?UserBalanceHistory
    {
        return $this->balance;
    }

    public function getManager(): Manager
    {
        return $this->manager;
    }

    public function getDateofadded(): ?\DateTime
    {
        return $this->dateofadded;
    }

    public function getSumm(): ?string
    {
        return $this->summ;
    }

    public function getCheckSumm(): ?string
    {
        return $this->check_summ;
    }

    public function getKassaId(): ?int
    {
        return $this->kassa_id;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function getFiscalTime(): ?string
    {
        return $this->fiscal_time;
    }

    public function getFiscalSumm(): ?string
    {
        return $this->fiscal_summ;
    }

    public function getFiscalFabricNumber(): ?string
    {
        return $this->fiscal_fabric_number;
    }

    public function getFiscalDocNumber(): ?string
    {
        return $this->fiscal_doc_number;
    }

    public function getFiscalDocType(): ?string
    {
        return $this->fiscal_doc_type;
    }

    public function getFiscalType(): ?int
    {
        return $this->fiscal_type;
    }

    public function getFiscalSmenaNumber(): ?string
    {
        return $this->fiscal_smena_number;
    }

    public function getFiscalCheckNumber(): ?string
    {
        return $this->fiscal_check_number;
    }

    public function getFiscalRegisterNumber(): ?string
    {
        return $this->fiscal_register_number;
    }

    public function getErrorDescription(): ?string
    {
        return $this->error_description;
    }
}
