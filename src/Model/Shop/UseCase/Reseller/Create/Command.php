<?php

namespace App\Model\Shop\UseCase\Reseller\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $name;
}
