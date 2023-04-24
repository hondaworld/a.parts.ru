<?php

namespace App\Model\Order\Entity\Alert;

use App\Model\Order\Entity\AlertType\OrderAlertType;
use App\Model\Order\Entity\Good\OrderGood;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderAlertRepository::class)
 * @ORM\Table(name="order_alerts")
 */
class OrderAlert
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="alertID")
     */
    private $alertID;

    /**
     * @var OrderAlertType
     * @ORM\ManyToOne(targetEntity="App\Model\Order\Entity\AlertType\OrderAlertType", inversedBy="alerts")
     * @ORM\JoinColumn(name="typeID", referencedColumnName="typeID", nullable=false)
     */
    private $type;

    /**
     * @var OrderGood
     * @ORM\ManyToOne(targetEntity="App\Model\Order\Entity\Good\OrderGood", inversedBy="alerts")
     * @ORM\JoinColumn(name="goodID", referencedColumnName="goodID", nullable=false)
     */
    private $good;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    public function __construct(OrderAlertType $type, OrderGood $good)
    {
        $this->type = $type;
        $this->good = $good;
        $this->dateofadded = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->alertID;
    }

    public function getType(): OrderAlertType
    {
        return $this->type;
    }

    public function getGood(): OrderGood
    {
        return $this->good;
    }

    public function getDateofadded(): ?\DateTimeInterface
    {
        return $this->dateofadded;
    }
}
