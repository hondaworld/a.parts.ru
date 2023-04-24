<?php

namespace App\Model\Ticket\UseCase\ClientTicketGroup\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    public $isHideUser;

    public $isClose;

    /**
     * @var array
     */
    public $managers;
}
