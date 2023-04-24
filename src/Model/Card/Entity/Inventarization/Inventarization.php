<?php

namespace App\Model\Card\Entity\Inventarization;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=InventarizationRepository::class)
 * @ORM\Table(name="inventarizations")
 */
class Inventarization
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="inventarizationID")
     */
    private $inventarizationID;

    /**
     * @ORM\Column(type="date")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="boolean", name="isClose")
     */
    private $isClose = false;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $dateofclosed;

    /**
     * @var InventarizationGood[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Card\Entity\Inventarization\InventarizationGood", mappedBy="inventarization", orphanRemoval=true)
     */
    private $goods;

    public function __construct(\DateTime $dateofadded)
    {
        $this->dateofadded = $dateofadded;
    }

    public function update(\DateTime $dateofadded)
    {
        $this->dateofadded = $dateofadded;
    }

    public function closed()
    {
        $this->isClose = true;
        $this->dateofclosed = new \DateTime();
    }

    public function getId(): int
    {
        return $this->inventarizationID;
    }

    public function getDateofadded(): \DateTime
    {
        return $this->dateofadded;
    }

    public function isClose(): bool
    {
        return $this->isClose;
    }

    public function getDateofclosed(): ?\DateTimeInterface
    {
        return $this->dateofclosed;
    }

    /**
     * @return ArrayCollection|InventarizationGood[]
     */
    public function getGoods()
    {
        return $this->goods->toArray();
    }


}
