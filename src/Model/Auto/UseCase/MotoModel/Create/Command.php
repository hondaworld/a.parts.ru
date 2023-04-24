<?php

namespace App\Model\Auto\UseCase\MotoModel\Create;

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
    public $moto_groupID;
}
