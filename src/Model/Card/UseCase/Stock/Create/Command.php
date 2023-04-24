<?php

namespace App\Model\Card\UseCase\Stock\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @var string
     */
    public $text;

    /**
     * @var array
     */
    public $providers;

}
