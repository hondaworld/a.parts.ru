<?php

namespace App\Model\Provider\Entity\ProviderInvoice;

use App\Model\Provider\Entity\LogInvoice\LogInvoice;
use App\Model\Provider\Entity\LogInvoiceAll\LogInvoiceAll;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Shop\Entity\DeleteReason\DeleteReason;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProviderInvoiceRepository::class)
 * @ORM\Table(name="providerInvoices")
 */
class ProviderInvoice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="providerInvoiceID")
     */
    private $providerInvoiceID;

    /**
     * @var Provider
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\Provider\Provider", inversedBy="invoices")
     * @ORM\JoinColumn(name="providerID", referencedColumnName="providerID")
     */
    private $provider;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofchanged;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $price_email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email_from;

    /**
     * @ORM\Column(type="string", length=25)
     */
    private $status_from;

    /**
     * @ORM\Column(type="integer")
     */
    private $status_to;

    /**
     * @ORM\Column(type="integer")
     */
    private $status_none;

    /**
     * @var DeleteReason
     * @ORM\ManyToOne(targetEntity="App\Model\Shop\Entity\DeleteReason\DeleteReason")
     * @ORM\JoinColumn(name="deleteReasonID", referencedColumnName="deleteReasonID")
     */
    private $deleteReason;

    /**
     * @var Num
     * @ORM\Embedded(class="Num")
     */
    private $num;

    /**
     * @ORM\Column(type="koef", precision=7, scale=2, name="priceAdd")
     */
    private $priceAdd = 0;

    /**
     * @ORM\Column(type="boolean", name="isHide")
     */
    private $isHide = false;

    /**
     * @var LogInvoice[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Provider\Entity\LogInvoice\LogInvoice", mappedBy="providerInvoice", orphanRemoval=true)
     */
    private $logs;

    /**
     * @var LogInvoiceAll[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Provider\Entity\LogInvoiceAll\LogInvoiceAll", mappedBy="providerInvoice")
     */
    private $invoiceAlls;

    public function __construct(Provider $provider, string $status_from, int $status_to, int $status_none, DeleteReason $deleteReason, ?string $price, ?string $price_email, ?string $email_from, ?string $priceAdd, Num $num)
    {
        $this->provider = $provider;
        $this->status_from = $status_from;
        $this->status_to = $status_to;
        $this->status_none = $status_none;
        $this->deleteReason = $deleteReason;
        $this->price = $price ?: '';
        $this->price_email = $price_email ?: '';
        $this->email_from = $email_from ?: '';
        $this->priceAdd = $priceAdd ?: '';
        $this->num = $num;
    }

    public function update(string $status_from, int $status_to, int $status_none, DeleteReason $deleteReason, string $price, string $price_email, string $email_from, string $priceAdd, Num $num)
    {
        $this->status_from = $status_from;
        $this->status_to = $status_to;
        $this->status_none = $status_none;
        $this->deleteReason = $deleteReason;
        $this->price = $price;
        $this->price_email = $price_email;
        $this->email_from = $email_from;
        $this->priceAdd = $priceAdd;
        $this->num = $num;
    }

    public function getId(): ?int
    {
        return $this->providerInvoiceID;
    }

    public function getProvider(): Provider
    {
        return $this->provider;
    }

    public function getDateofchanged(): ?\DateTimeInterface
    {
        return $this->dateofchanged;
    }

    public function getPrice(): string
    {
        return $this->price;
    }

    public function getPriceEmail(): string
    {
        return $this->price_email;
    }

    public function getEmailFrom(): string
    {
        return $this->email_from;
    }

    public function getStatusFrom(): string
    {
        return $this->status_from;
    }

    public function getStatusTo(): int
    {
        return $this->status_to;
    }

    public function getStatusNone(): int
    {
        return $this->status_none;
    }

    public function getDeleteReason(): DeleteReason
    {
        return $this->deleteReason;
    }

    /**
     * @return Num
     */
    public function getNum(): Num
    {
        return $this->num;
    }

    public function getPriceAdd(): int
    {
        return $this->priceAdd;
    }

    public function isHide(): bool
    {
        return $this->isHide;
    }

    public function hide(): void
    {
        $this->isHide = true;
    }

    public function unHide(): void
    {
        $this->isHide = false;
    }
}
