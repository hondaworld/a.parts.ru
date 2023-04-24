<?php

namespace App\Model\Order\UseCase\ShippingPlace\Edit;

use App\Model\Expense\Entity\ShippingPlace\ShippingPlace;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $shipping_placeID;

    /**
     * @Assert\NotBlank()
     */
    public $number;

    /**
     * @Assert\NotBlank()
     */
    public $length;

    /**
     * @Assert\NotBlank()
     */
    public $width;

    /**
     * @Assert\NotBlank()
     */
    public $height;

    /**
     * @Assert\NotBlank()
     */
    public $weight;

    public function __construct(int $shipping_placeID)
    {
        $this->shipping_placeID = $shipping_placeID;
    }

    public static function fromEntity(ShippingPlace $shippingPlace): self
    {
        $command = new self($shippingPlace->getId());
        $command->number = $shippingPlace->getNumber();
        $command->length = $shippingPlace->getLength();
        $command->width = $shippingPlace->getWidth();
        $command->height = $shippingPlace->getHeight();
        $command->weight = $shippingPlace->getWeight();
        return $command;
    }
}
