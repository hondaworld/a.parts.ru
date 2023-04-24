<?php

namespace App\Model\Card\UseCase\Category\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $name;
}
