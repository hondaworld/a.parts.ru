<?php

namespace App\Model\Expense\UseCase\ShippingPlace\Edit;

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

    /**
     * @var string
     */
    public $photo1;

    /**
     * @var string
     */
    public $photo2;

    public function __construct(int $shipping_placeID)
    {
        $this->shipping_placeID = $shipping_placeID;
    }

    public static function fromEntity(ShippingPlace $shippingPlace, string $attachDirectory): self
    {
        $command = new self($shippingPlace->getId());
        $command->number = $shippingPlace->getNumber();
        $command->length = $shippingPlace->getLength();
        $command->width = $shippingPlace->getWidth();
        $command->height = $shippingPlace->getHeight();
        $command->weight = $shippingPlace->getWeight();
        $command->photo1 = $shippingPlace->getPhoto1() ? $attachDirectory . $shippingPlace->getPhoto1() : '';
        $command->photo2 = $shippingPlace->getPhoto2() ? $attachDirectory . $shippingPlace->getPhoto2() : '';
        return $command;
    }
}
