<?php

namespace App\Model\Auto\UseCase\WorkPeriod\Create;

use App\Model\Auto\Entity\Modification\AutoModification;
use App\Model\Flusher;
use App\Model\Work\Entity\Period\WorkPeriod;
use App\Model\Work\Entity\Period\WorkPeriodRepository;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(WorkPeriodRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command, AutoModification $autoModification): void
    {
        $workPeriod = new WorkPeriod($autoModification, $command->name, $command->norma ?: 0, $this->repository->getNextSort($autoModification));
        $this->repository->add($workPeriod);
        $this->flusher->flush();
    }
}
