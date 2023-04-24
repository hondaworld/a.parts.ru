<?php

namespace App\Model\Work\UseCase\Group\Create;

use App\Model\Flusher;
use App\Model\Work\Entity\Group\WorkGroup;
use App\Model\Work\Entity\Group\WorkGroupRepository;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(WorkGroupRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $workGroup = new WorkGroup($command->workCategory, $command->name, $command->norma, $command->isTO, $command->sort);
        $this->repository->add($workGroup);
        $this->flusher->flush();
    }
}
