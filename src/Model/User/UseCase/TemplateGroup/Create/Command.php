<?php

namespace App\Model\User\UseCase\TemplateGroup\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $name;
}
