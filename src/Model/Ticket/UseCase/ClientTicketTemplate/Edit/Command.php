<?php

namespace App\Model\Ticket\UseCase\ClientTicketTemplate\Edit;

use App\Model\Ticket\Entity\ClientTicketGroup\ClientTicketGroup;
use App\Model\Ticket\Entity\ClientTicketTemplate\ClientTicketTemplate;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $templateID;

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

    public function __construct(int $templateID)
    {
        $this->templateID = $templateID;
    }

    public static function fromEntity(ClientTicketTemplate $clientTicketTemplate): self
    {
        $command = new self($clientTicketTemplate->getId());
        $command->name = $clientTicketTemplate->getName();
        $command->text = $clientTicketTemplate->getText();
        $command->client_ticket_groups = array_map(function (ClientTicketGroup $clientTicketGroup): int {
            return $clientTicketGroup->getId();
        }, $clientTicketTemplate->getClientTicketGroups());
        return $command;
    }
}
