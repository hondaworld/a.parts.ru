<?php

namespace App\Model\Auto\UseCase\Modification\Create;

use App\Model\Auto\Entity\Generation\AutoGeneration;
use App\Model\Auto\Entity\Modification\AutoModification;
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

    public function handle(Command $command, AutoGeneration $autoGeneration): void
    {
        $autoModification = new AutoModification($autoGeneration, $command->name);

        $this->repository->add($autoModification);

        $this->flusher->flush();
    }
}
