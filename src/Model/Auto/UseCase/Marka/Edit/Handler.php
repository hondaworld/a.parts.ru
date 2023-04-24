<?php

namespace App\Model\Auto\UseCase\Marka\Edit;

use App\Model\Auto\Entity\Marka\AutoMarkaRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(AutoMarkaRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $autoMarka = $this->repository->get($command->auto_markaID);

        $autoMarka->update($command->name, $command->name_rus);

        $this->flusher->flush();
    }
}
