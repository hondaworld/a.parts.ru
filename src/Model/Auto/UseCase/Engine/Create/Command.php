<?php

namespace App\Model\Auto\UseCase\Engine\Create;

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
    public $url;

    public $description_tuning;
}
