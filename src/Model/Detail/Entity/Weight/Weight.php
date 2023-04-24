<?php

namespace App\Model\Detail\Entity\Weight;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=WeightRepository::class)
 * @ORM\Table(name="weights")
 */
class Weight
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="weightID")
     */
    private $weightID;

    /**
     * @var DetailNumber
     * @ORM\Column(type="detail_number", length=30)
     */
    private $number;

    /**
     * @var Creater
     * @ORM\ManyToOne(targetEntity="App\Model\Detail\Entity\Creater\Creater", inversedBy="weights")
     * @ORM\JoinColumn(name="createrID", referencedColumnName="createrID", nullable=false)
     */
    private $creater;

    /**
     * @ORM\Column(type="koef", precision=7, scale=4)
     */
    private $weight;

    /**
     * @ORM\Column(type="boolean", name="weightIsReal")
     */
    private $weightIsReal;

    public function __construct(DetailNumber $number, Creater $creater, string $weight, bool $weightIsReal)
    {
        $this->number = $number;
        $this->creater = $creater;
        $this->weight = $weight;
        $this->weightIsReal = $weightIsReal;
    }

    public function update(string $weight, bool $weightIsReal)
    {
        $this->weight = $weight;
        $this->weightIsReal = $weightIsReal;
    }

    public function updateWeight(string $weight)
    {
        $this->weight = $weight;
    }

    public function getId(): ?int
    {
        return $this->weightID;
    }

    public function getNumber(): DetailNumber
    {
        return $this->number;
    }

    public function getCreater(): Creater
    {
        return $this->creater;
    }

    public function getWeight(): ?string
    {
        return $this->weight;
    }

    public function getWeightIsReal(): ?bool
    {
        return $this->weightIsReal;
    }
}
