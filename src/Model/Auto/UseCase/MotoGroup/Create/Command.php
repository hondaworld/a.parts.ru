<?php

namespace App\Model\Auto\UseCase\MotoGroup\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     */
    public $photo;
}
