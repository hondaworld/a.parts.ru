<?php

namespace App\Model\Expense\Entity\ShippingStatus;

use App\Model\Expense\Entity\Shipping\Shipping;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ShippingStatusRepository::class)
 * @ORM\Table(name="shipping_statuses")
 */
class ShippingStatus
{
    public const SENT_STATUS = 6;
    public const PICKING_STATUS = 1;
    public const PICKED_STATUS = 2;
    public const DOCUMENTS_DONE = 3;
    public const REQUEST_TK = 4;
    public const SENT_TK = 5;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer", name="status")
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $number;

    /**
     * @var Shipping[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="App\Model\Expense\Entity\Shipping\Shipping", mappedBy="status")
     */
    private $shippings;

    public function getId(): int
    {
        return $this->status;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getNumber(): int
    {
        return $this->number;
    }
}
