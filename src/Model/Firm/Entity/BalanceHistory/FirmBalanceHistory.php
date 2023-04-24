<?php

namespace App\Model\Firm\Entity\BalanceHistory;

use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Income\Entity\Document\IncomeDocument;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\User\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FirmBalanceHistoryRepository::class)
 * @ORM\Table(name="firmBalanceHistory")
 */
class FirmBalanceHistory
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="balanceID")
     */
    private $balanceID;

    /**
     * @var Provider
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\Provider\Provider", inversedBy="firm_balance_history")
     * @ORM\JoinColumn(name="providerID", referencedColumnName="providerID", nullable=false)
     */
    private $provider;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="firm_balance_history")
     * @ORM\JoinColumn(name="userID", referencedColumnName="userID", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="koef", precision=11, scale=2)
     */
    private $balance;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2)
     */
    private $balance_nds;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="firm_balance_history")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=false)
     */
    private $manager;

    /**
     * @var Firm
     * @ORM\ManyToOne(targetEntity="App\Model\Firm\Entity\Firm\Firm", inversedBy="firm_balance_history")
     * @ORM\JoinColumn(name="firmID", referencedColumnName="firmID", nullable=false)
     */
    private $firm;


    /**
     * @var IncomeDocument
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Document\IncomeDocument", inversedBy="firm_balance_history")
     * @ORM\JoinColumn(name="incomeDocumentID", referencedColumnName="incomeDocumentID", nullable=true)
     */
    private $incomeDocument;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    public function __construct(Provider $provider, User $user, string $balance, string $balance_nds, Manager $manager, ?string $description, ?IncomeDocument $incomeDocument, Firm $firm)
    {
        $this->provider = $provider;
        $this->user = $user;
        $this->balance = $balance;
        $this->balance_nds = $balance_nds;
        $this->manager = $manager;
        $this->description = $description ?: '';
        $this->incomeDocument = $incomeDocument;
        $this->firm = $firm;
        $this->dateofadded = new \DateTime();
    }

    public function updateFirm(Firm $firm)
    {
        $this->firm = $firm;
    }

    public function updateBalance(string $balance, ?string $description)
    {
        $this->balance = $balance;
        $this->description = $description ?: '';
    }

    public function updateBalanceNds(?string $balance_nds)
    {
        $this->balance_nds = $balance_nds ?: 0;
    }

    public function getId(): ?int
    {
        return $this->balanceID;
    }

    public function getProvider(): Provider
    {
        return $this->provider;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getBalance(): ?string
    {
        return $this->balance;
    }

    public function getBalanceNds(): ?string
    {
        return $this->balance_nds;
    }

    public function getManager(): Manager
    {
        return $this->manager;
    }

    public function getFirm(): Firm
    {
        return $this->firm;
    }

    public function getIncomeDocument(): ?IncomeDocument
    {
        return $this->incomeDocument;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }
}
