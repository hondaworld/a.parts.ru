<?php

namespace App\Model\Ticket\UseCase\ClientTicketTemplate\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    public $text;

    /**
     * @var array
     */
    public $client_ticket_groups;
}
