<?php

namespace App\Model\Income\Entity\StatusHistory;

use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Manager\Entity\Manager\Manager;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=IncomeStatusHistoryRepository::class)
 * @ORM\Table(name="income_status_history")
 */
class IncomeStatusHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="historyID")
     */
    private $historyID;

    /**
     * @var IncomeStatus
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Status\IncomeStatus", inversedBy="incomes", fetch="EAGER")
     * @ORM\JoinColumn(name="status", referencedColumnName="status", nullable=false)
     */
    private $status;

    /**
     * @var Income
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Income\Income", inversedBy="income_status_history")
     * @ORM\JoinColumn(name="incomeID", referencedColumnName="incomeID", nullable=false)
     */
    private $income;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="income_status_history")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=false)
     */
    private $manager;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    public function __construct(IncomeStatus $status, Income $income, Manager $manager)
    {
        $this->status = $status;
        $this->income = $income;
        $this->manager = $manager;
        $this->dateofadded = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->historyID;
    }

    public function getStatus(): IncomeStatus
    {
        return $this->status;
    }

    public function getIncome(): Income
    {
        return $this->income;
    }

    public function getManager(): Manager
    {
        return $this->manager;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }
}
