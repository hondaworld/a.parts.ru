<?php

namespace App\Model\User\UseCase\ShopPayType\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $name;
}
