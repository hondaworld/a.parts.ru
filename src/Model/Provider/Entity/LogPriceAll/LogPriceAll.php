<?php

namespace App\Model\Provider\Entity\LogPriceAll;

use App\Model\Provider\Entity\Price\ProviderPrice;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LogPriceAllRepository::class)
 * @ORM\Table(name="logPrice_all")
 */
class LogPriceAll
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="logPriceID")
     */
    private $logPriceID;

    /**
     * @var ProviderPrice
     * @ORM\ManyToOne(targetEntity="App\Model\Provider\Entity\Price\ProviderPrice", inversedBy="logs_all")
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
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="integer", name="countDelete")
     */
    private $countDelete;

    /**
     * @ORM\Column(type="integer", name="countInsert")
     */
    private $countInsert;

    /**
     * @ORM\Column(type="text")
     */
    private $comment;

    public function __construct(ProviderPrice $providerPrice, int $countDelete, int $countInsert, string $comment)
    {
        $this->providerPrice = $providerPrice;
        $this->providerName = $providerPrice->getFullName();
        $this->price = $providerPrice->getPrice()->getPrice();
        $this->countDelete = $countDelete;
        $this->countInsert = $countInsert;
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

    public function getDateofadded(): \DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getCountDelete(): ?int
    {
        return $this->countDelete;
    }

    public function getCountInsert(): ?int
    {
        return $this->countInsert;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
