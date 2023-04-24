<?php

namespace App\Model\Detail\UseCase\KitNumber\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $number;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $quantity;
}
