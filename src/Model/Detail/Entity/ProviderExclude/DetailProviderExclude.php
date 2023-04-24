<?php

namespace App\Model\Detail\Entity\ProviderExclude;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DetailProviderExcludeRepository::class)
 * @ORM\Table(name="numberDaysExclude")
 */
class DetailProviderExclude
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="excludeID")
     */
    private $excludeID;

    /**
     * @var DetailNumber
     * @ORM\Column(type="detail_number", length=30)
     */
    private $number;

    /**
     * @var Creater
     * @ORM\ManyToOne(targetEntity="App\Model\Detail\Entity\Creater\Creater", inversedBy="providerExclude")
     * @ORM\JoinColumn(name="createrID", referencedColumnName="createrID", nullable=false)
     */
    private $creater;

    /**
     * @ORM\Column(type="integer", name="providerID")
     */
    private $providerID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $comment;

    public function __construct(DetailNumber $number, Creater $creater, int $providerID, ?string $comment)
    {
        $this->number = $number;
        $this->creater = $creater;
        $this->providerID = $providerID;
        $this->comment = $comment ?: '';
    }

    public function update(?string $comment)
    {
        $this->comment = $comment ?: '';
    }

    public function getId(): ?int
    {
        return $this->excludeID;
    }

    public function getNumber(): DetailNumber
    {
        return $this->number;
    }

    public function getCreater(): Creater
    {
        return $this->creater;
    }

    public function getProviderID(): ?int
    {
        return $this->providerID;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}
