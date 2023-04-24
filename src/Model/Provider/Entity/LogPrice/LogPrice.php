<?php

namespace App\Model\Provider\Entity\LogPrice;

use App\Model\Provider\Entity\Price\ProviderPrice;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LogPriceRepository::class)
 * @ORM\Table(name="logPrice")
 */
class LogPrice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="logPriceID")
     */
    private $logPriceID;

    /**
     * @var ProviderPrice
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\Price\ProviderPrice", inversedBy="logs")
     * @ORM\JoinColumn(name="providerPriceID", referencedColumnName="providerPriceID")
     */
    private $providerPrice;

    /**
     * @ORM\Column(type="string", length=255, name="providerName")
     */
    private $providerName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255, name="createrName")
     */
    private $createrName;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $comment;

    public function __construct(ProviderPrice $providerPrice, string $createrName, string $comment)
    {
        $this->providerPrice = $providerPrice;
        $this->providerName = $providerPrice->getFullName();
        $this->price = $providerPrice->getPrice()->getPrice();
        $this->createrName = $createrName;
        $this->dateofadded = new \DateTime();
        $this->comment = $comment;
    }

    public function getId(): ?int
    {
        return $this->logPriceID;
    }

    public function getProviderPrice(): ProviderPrice
    {
        return $this->providerPrice;
    }

    public function getProviderName(): ?string
    {
        return $this->providerName;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function getCreaterName(): ?string
    {
        return $this->createrName;
    }

    public function getDateofadded(): \DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
