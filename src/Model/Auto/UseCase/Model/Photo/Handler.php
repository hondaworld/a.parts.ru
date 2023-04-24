<?php

namespace App\Model\Auto\UseCase\Model\Photo;

use App\Model\Auto\Entity\Model\AutoModelRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(AutoModelRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $autoModel = $this->repository->get($command->autoModelID);

        $autoModel->updatePhoto($command->photo);

        $this->flusher->flush();
    }
}
