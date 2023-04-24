<?php

namespace App\Model\Order\Entity\AlertType;

use App\Model\Order\Entity\Alert\OrderAlert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderAlertTypeRepository::class)
 * @ORM\Table(name="order_alert_types")
 */
class OrderAlertType
{
    public const CHANGE_STATUS = 1;
    public const PURCHASE = 2;
    public const MOVING = 3;
    public const REMOVE_RESERVE = 4;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="typeID")
     */
    private $typeID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var OrderAlert[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Alert\OrderAlert", mappedBy="type")
     */
    private $alerts;

    public function getId(): ?int
    {
        return $this->typeID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
