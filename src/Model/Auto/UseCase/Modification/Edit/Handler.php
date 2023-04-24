<?php

namespace App\Model\Auto\UseCase\Modification\Edit;

use App\Model\Auto\Entity\Modification\AutoModificationRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(AutoModificationRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $autoModification = $this->repository->get($command->auto_modificationID);

        $autoModification->update($command->name);

        $this->flusher->flush();
    }
}
