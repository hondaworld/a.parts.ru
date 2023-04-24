<?php

namespace App\Model\Auto\UseCase\WorkPeriod\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $name;

    public $norma;
}
