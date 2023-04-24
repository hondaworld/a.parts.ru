<?php

namespace App\Model\Order\Entity\AddReason;

use App\Model\Order\Entity\Order\Order;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=OrderAddReasonRepository::class)
 * @ORM\Table(name="order_add_reasons")
 */
class OrderAddReason
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="order_add_reasonID")
     */
    private $order_add_reasonID;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @var Order[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Order\Entity\Order\Order", mappedBy="order_add_reason")
     */
    private $orders;

    public function getId(): ?int
    {
        return $this->order_add_reasonID;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
