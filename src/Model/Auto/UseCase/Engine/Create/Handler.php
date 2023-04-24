<?php

namespace App\Model\Auto\UseCase\Engine\Create;

use App\Model\Auto\Entity\Engine\AutoEngine;
use App\Model\Auto\Entity\Engine\AutoEngineRepository;
use App\Model\Auto\Entity\Generation\AutoGeneration;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(AutoEngineRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command, AutoGeneration $autoGeneration): void
    {
        $autoEngine = new AutoEngine($autoGeneration, $command->name, $command->url, $command->description_tuning);

        $this->repository->add($autoEngine);

        $this->flusher->flush();
    }
}
