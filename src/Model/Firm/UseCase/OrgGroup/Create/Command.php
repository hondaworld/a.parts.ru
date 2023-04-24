<?php

namespace App\Model\Firm\UseCase\OrgGroup\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    public $isMain;
}
