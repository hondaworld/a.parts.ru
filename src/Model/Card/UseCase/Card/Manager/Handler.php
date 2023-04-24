<?php

namespace App\Model\Card\UseCase\Card\Manager;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;

class Handler
{
    private ZapCardRepository $repository;
    private Flusher $flusher;
    private ManagerRepository $managerRepository;

    public function __construct(
        ZapCardRepository $repository,
        ManagerRepository $managerRepository,
        Flusher           $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->managerRepository = $managerRepository;
    }

    public function handle(Command $command): void
    {
        $zapCard = $this->repository->get($command->zapCardID);

        $zapCard->updateManager(
            $command->managerID ? $this->managerRepository->get($command->managerID) : null
        );

        $this->flusher->flush();
    }
}
