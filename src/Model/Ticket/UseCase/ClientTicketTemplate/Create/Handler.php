<?php

namespace App\Model\Ticket\UseCase\ClientTicketTemplate\Create;

use App\Model\Flusher;
use App\Model\Ticket\Entity\ClientTicketGroup\ClientTicketGroupRepository;
use App\Model\Ticket\Entity\ClientTicketTemplate\ClientTicketTemplate;
use App\Model\Ticket\Entity\ClientTicketTemplate\ClientTicketTemplateRepository;

class Handler
{
    private ClientTicketGroupRepository $clientTicketGroupRepository;
    private Flusher $flusher;
    private ClientTicketTemplateRepository $clientTicketTemplateRepository;

    public function __construct(ClientTicketGroupRepository $clientTicketGroupRepository, ClientTicketTemplateRepository $clientTicketTemplateRepository, Flusher $flusher)
    {
        $this->clientTicketGroupRepository = $clientTicketGroupRepository;
        $this->flusher = $flusher;
        $this->clientTicketTemplateRepository = $clientTicketTemplateRepository;
    }

    public function handle(Command $command): void
    {
        $clientTicketTemplate = new ClientTicketTemplate(
            $command->name,
            $command->text
        );

        foreach ($command->client_ticket_groups as $groupID) {
            $clientTicketGroup = $this->clientTicketGroupRepository->get($groupID);
            $clientTicketTemplate->assignClientTicketGroup($clientTicketGroup);
        }

        $this->clientTicketTemplateRepository->add($clientTicketTemplate);

        $this->flusher->flush();
    }
}
