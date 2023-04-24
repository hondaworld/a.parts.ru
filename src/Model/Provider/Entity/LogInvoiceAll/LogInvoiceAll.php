<?php

namespace App\Model\Provider\Entity\LogInvoiceAll;

use App\Model\Provider\Entity\LogInvoice\LogInvoice;
use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LogInvoiceAllRepository::class)
 * @ORM\Table(name="logInvoiceAll")
 */
class LogInvoiceAll
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="logInvoiceAllID")
     */
    private $logInvoiceAllID;

    /**
     * @var ProviderInvoice
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice", inversedBy="invoiceAlls")
     * @ORM\JoinColumn(name="providerInvoiceID", referencedColumnName="providerInvoiceID")
     */
    private $providerInvoice;

    /**
     * @ORM\Column(type="string", length=255, name="providerName")
     */
    private $providerName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="boolean", name="isDone")
     */
    private $isDone = false;

    /**
     * @var LogInvoice[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Provider\Entity\LogInvoice\LogInvoice", mappedBy="invoiceAll", orphanRemoval=true, cascade={"all"})
     */
    private $logs;

    public function __construct(ProviderInvoice $providerInvoice)
    {
        $this->providerInvoice = $providerInvoice;
        $this->providerName = $providerInvoice->getProvider()->getName();
        $this->dateofadded = new \DateTime();
        $this->logs = new ArrayCollection();
    }

    public function updateIsDone(bool $isDone): void
    {
        $this->isDone = $isDone;
    }

    public function getId(): ?int
    {
        return $this->logInvoiceAllID;
    }

    public function getProviderInvoice(): ProviderInvoice
    {
        return $this->providerInvoice;
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function isDone(): bool
    {
        return $this->isDone;
    }

    /**
     * @return LogInvoice[]|ArrayCollection
     */
    public function getLogs()
    {
        return $this->logs->toArray();
    }

    /**
     * @param LogInvoice $logInvoice
     */
    public function assignLog(LogInvoice $logInvoice): void
    {
        $logInvoice->updateInvoiceAll($this);
        $this->logs->add($logInvoice);
    }
}
