<?php

namespace App\Model\Auto\UseCase\Model\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $name;

    /**
     * @Assert\NotBlank()
     */
    public $name_rus;

    public $path;
}
