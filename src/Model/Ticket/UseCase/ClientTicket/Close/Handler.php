<?php

namespace App\Model\Ticket\UseCase\ClientTicket\Close;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Ticket\Entity\ClientTicket\ClientTicketRepository;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private ClientTicketRepository $clientTicketRepository;
    private ManagerRepository $managerRepository;

    public function __construct(
        ClientTicketRepository       $clientTicketRepository,
        ManagerRepository            $managerRepository,
        Flusher                      $flusher
    )
    {
        $this->flusher = $flusher;
        $this->clientTicketRepository = $clientTicketRepository;
        $this->managerRepository = $managerRepository;
    }

    public function handle(): void
    {
        $tickets = $this->clientTicketRepository->findNotClosed();
        foreach ($tickets as $ticket) {
            try {
                $manager = $this->managerRepository->get($ticket->getAnswer());
            } catch (DomainException $e) {
                $manager = $this->managerRepository->get(Manager::SUPER_ADMIN);
            }
            $ticket->close($manager);
        }

        $this->flusher->flush();
    }
}
