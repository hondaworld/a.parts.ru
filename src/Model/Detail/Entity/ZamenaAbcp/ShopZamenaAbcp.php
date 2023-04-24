<?php

namespace App\Model\Detail\Entity\ZamenaAbcp;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShopZamenaAbcpRepository::class)
 * @ORM\Table(name="shopZamenaAbcp")
 */
class ShopZamenaAbcp
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="id")
     */
    private $id;

    /**
     * @var DetailNumber
     * @ORM\Column(type="detail_number", length=20)
     */
    private $number;

    /**
     * @var DetailNumber
     * @ORM\Column(type="detail_number", length=20)
     */
    private $number2;

    /**
     * @var Creater
     * @ORM\ManyToOne(targetEntity="App\Model\Detail\Entity\Creater\Creater", inversedBy="zamena")
     * @ORM\JoinColumn(name="createrID", referencedColumnName="createrID", nullable=false)
     */
    private $creater;

    /**
     * @var Creater
     * @ORM\ManyToOne(targetEntity="App\Model\Detail\Entity\Creater\Creater", inversedBy="zamena2")
     * @ORM\JoinColumn(name="createrID2", referencedColumnName="createrID", nullable=false)
     */
    private $creater2;

    /**
     * @ORM\Column(type="date", name="dateofchanged")
     */
    private $dateofchanged;

    public function __construct(DetailNumber $number, Creater $creater, DetailNumber $number2, Creater $creater2)
    {
        $this->number = $number;
        $this->creater = $creater;
        $this->number2 = $number2;
        $this->creater2 = $creater2;
        $this->dateofchanged = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNumber(): DetailNumber
    {
        return $this->number;
    }

    public function getNumber2(): DetailNumber
    {
        return $this->number2;
    }

    public function getCreater(): Creater
    {
        return $this->creater;
    }

    public function getCreater2(): Creater
    {
        return $this->creater2;
    }

    public function getDateofchanged(): \DateTime
    {
        return $this->dateofchanged;
    }

}
