<?php

namespace App\Model\Provider\Entity\LogInvoice;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Income\Entity\Income\Income;
use App\Model\Provider\Entity\LogInvoiceAll\LogInvoiceAll;
use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LogInvoiceRepository::class)
 * @ORM\Table(name="logInvoice")
 */
class LogInvoice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="logInvoiceID")
     */
    private $logInvoiceID;

    /**
     * @var LogInvoiceAll
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\LogInvoiceAll\LogInvoiceAll", inversedBy="logs")
     * @ORM\JoinColumn(name="logInvoiceAllID", referencedColumnName="logInvoiceAllID")
     */
    private $invoiceAll;

    /**
     * @var ProviderInvoice
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice", inversedBy="logs")
     * @ORM\JoinColumn(name="providerInvoiceID", referencedColumnName="providerInvoiceID")
     */
    private $providerInvoice;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $comment;

    /**
     * @var DetailNumber
     * @ORM\Column(type="detail_number", length=30)
     */
    private $number;

    /**
     * @var Income
     * @ORM\ManyToOne(targetEntity="App\Model\Income\Entity\Income\Income")
     * @ORM\JoinColumn(name="incomeID", referencedColumnName="incomeID", nullable=true)
     */
    private $income;

    /**
     * @ORM\Column(type="integer", name="quantityIncome")
     */
    private $quantityIncome = 0;

    /**
     * @ORM\Column(type="integer", name="quantityInvoice")
     */
    private $quantityInvoice = 0;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2, name="priceIncome")
     */
    private $priceIncome = 0;

    /**
     * @ORM\Column(type="koef", precision=9, scale=2, name="priceInvoice")
     */
    private $priceInvoice;

    /**
     * @ORM\Column(type="integer", name="statusFrom")
     */
    private $statusFrom = 0;

    /**
     * @ORM\Column(type="integer", name="statusTo")
     */
    private $statusTo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $gtd = '';

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $country = '';

    public function __construct(ProviderInvoice $providerInvoice, DetailNumber $number, int $quantity, string $price)
    {
        $this->providerInvoice = $providerInvoice;
        $this->dateofadded = new \DateTime();
        $this->number = $number;
        $this->quantityInvoice = $quantity;
        $this->priceInvoice = $price;
        $this->statusTo = $providerInvoice->getStatusTo();
    }

    public function update(Income $income)
    {
        $this->income = $income;
        $this->quantityIncome = $income->getQuantity();
        $this->priceIncome = $income->getPriceZak();
        $this->statusFrom = $income->getStatus()->getId();
        $this->comment = '';
    }

    public function updateInvoiceAll(LogInvoiceAll $logInvoiceAll)
    {
        $this->invoiceAll = $logInvoiceAll;
    }

    public function updateCountry(?string $country)
    {
        $this->country = $country ?: '';
    }

    public function updateGtd(string $gtd)
    {
        $this->gtd = $gtd;
    }

    public function updateComment(?string $comment)
    {
        $this->comment = $comment ?: '';
    }

    public function getId(): ?int
    {
        return $this->logInvoiceID;
    }

    public function getInvoiceAll(): LogInvoiceAll
    {
        return $this->invoiceAll;
    }

    public function getProviderInvoice(): ProviderInvoice
    {
        return $this->providerInvoice;
    }

    public function getDateofadded(): \DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getNumber(): DetailNumber
    {
        return $this->number;
    }

    public function getIncome(): ?Income
    {
        return $this->income;
    }

    public function getQuantityIncome(): int
    {
        return $this->quantityIncome;
    }

    public function getQuantityInvoice(): int
    {
        return $this->quantityInvoice;
    }

    public function getPriceIncome(): float
    {
        return $this->priceIncome;
    }

    public function getPriceInvoice(): float
    {
        return $this->priceInvoice;
    }

    public function getStatusFrom(): int
    {
        return $this->statusFrom;
    }

    public function getStatusTo(): int
    {
        return $this->statusTo;
    }

    public function getGtd(): string
    {
        return $this->gtd;
    }

    public function getCountry(): string
    {
        return $this->country;
    }
}
