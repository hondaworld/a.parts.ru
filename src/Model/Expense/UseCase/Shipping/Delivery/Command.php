<?php

namespace App\Model\Expense\UseCase\Shipping\Delivery;

use App\Model\Expense\Entity\Shipping\Shipping;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $shippingID;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     */
    public $dateofadded;

    public $delivery_tkID;

    public $tracknumber;

    public $pay_type;

    public function __construct(int $shippingID)
    {
        $this->shippingID = $shippingID;
    }

    public static function fromEntity(Shipping $shipping): self
    {
        $command = new self($shipping->getId());
        $command->dateofadded = $shipping->getDateofadded();
        $command->delivery_tkID = $shipping->getDeliveryTk() ? $shipping->getDeliveryTk()->getId() : null;
        $command->tracknumber = $shipping->getTracknumber();
        $command->pay_type = $shipping->getPayType();
        return $command;
    }
}
