<?php

namespace App\Model\Shop\UseCase\DeleteReason\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $name;

    public $isMain;
}
