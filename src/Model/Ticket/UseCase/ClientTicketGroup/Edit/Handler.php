<?php

namespace App\Model\Ticket\UseCase\ClientTicketGroup\Edit;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Ticket\Entity\ClientTicketGroup\ClientTicketGroupRepository;

class Handler
{
    private ClientTicketGroupRepository $clientTicketGroupRepository;
    private Flusher $flusher;
    private ManagerRepository $managerRepository;

    public function __construct(ClientTicketGroupRepository $clientTicketGroupRepository, ManagerRepository $managerRepository, Flusher $flusher)
    {
        $this->clientTicketGroupRepository = $clientTicketGroupRepository;
        $this->flusher = $flusher;
        $this->managerRepository = $managerRepository;
    }

    public function handle(Command $command): void
    {
        $clientTicketGroup = $this->clientTicketGroupRepository->get($command->groupID);

        $clientTicketGroup->update(
            $command->name,
            $command->isHideUser,
            $command->isClose
        );

        $clientTicketGroup->clearManagers();
        foreach ($command->managers as $managerID) {
            $manager = $this->managerRepository->get($managerID);
            $clientTicketGroup->assignManager($manager);
        }

        $this->flusher->flush();
    }
}
