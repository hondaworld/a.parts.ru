<?php

namespace App\Model\Detail\Entity\Zamena;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Manager\Entity\Manager\Manager;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShopZamenaRepository::class)
 * @ORM\Table(name="shopZamena")
 */
class ShopZamena
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="shopZamenaID")
     */
    private $shopZamenaID;

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
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="zamena")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=true)
     */
    private $manager;

    public function __construct(DetailNumber $number, Creater $creater, DetailNumber $number2, Creater $creater2, Manager $manager)
    {
        $this->number = $number;
        $this->creater = $creater;
        $this->number2 = $number2;
        $this->creater2 = $creater2;
        $this->manager = $manager;
    }

    public function getId(): ?int
    {
        return $this->shopZamenaID;
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

    public function getManager(): ?Manager
    {
        return $this->manager;
    }
}
