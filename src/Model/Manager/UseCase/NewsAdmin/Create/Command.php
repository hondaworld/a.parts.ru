<?php

namespace App\Model\Manager\UseCase\NewsAdmin\Create;

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
    public $description;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $type;
}
