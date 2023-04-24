<?php

namespace App\Model\Order\UseCase\ShippingPlace\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
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

}
