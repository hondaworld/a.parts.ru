<?php

namespace App\Model\Order\Entity\ManagerOperation;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Order\Order;
use App\Model\User\Entity\User\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ManagerOrderOperationRepository::class)
 * @ORM\Table(name="managerOrderOperations")
 */
class ManagerOrderOperation
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Manager
     * @ORM\ManyToOne(targetEntity="App\Model\Manager\Entity\Manager\Manager", inversedBy="manager_order_operations")
     * @ORM\JoinColumn(name="managerID", referencedColumnName="managerID", nullable=false)
     */
    private $manager;

    /**
     * @var Order
     * @ORM\ManyToOne(targetEntity="App\Model\Order\Entity\Order\Order", inversedBy="manager_order_operations")
     * @ORM\JoinColumn(name="orderID", referencedColumnName="orderID", nullable=true)
     */
    private $order;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Model\User\Entity\User\User", inversedBy="manager_order_operations")
     * @ORM\JoinColumn(name="userID", referencedColumnName="userID", nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description = '';

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateofadded;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $number = '';

    public function __construct(Manager $manager, ?User $user, ?Order $order, string $description, string $number)
    {
        $this->manager = $manager;
        $this->user = $user;
        $this->order = $order;
        $this->description = $description;
        $this->number = $number;
        $this->dateofadded = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getManager(): Manager
    {
        return $this->manager;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDateofadded(): \DateTimeInterface
    {
        return $this->dateofadded;
    }

    public function getNumber(): string
    {
        return $this->number;
    }
}
