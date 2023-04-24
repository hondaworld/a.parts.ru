<?php

namespace App\Model\Auto\UseCase\Engine\Edit;

use App\Model\Auto\Entity\Engine\AutoEngineRepository;
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

    public function handle(Command $command): void
    {
        $autoEngine = $this->repository->get($command->auto_engineID);

        $autoEngine->update($command->name, $command->url, $command->description_tuning);

        $this->flusher->flush();
    }
}
