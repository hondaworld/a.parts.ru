<?php

namespace App\Model\Ticket\UseCase\ClientTicket\Answer;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $text;

    /**
     * @var string
     */
    public $attach;
}
