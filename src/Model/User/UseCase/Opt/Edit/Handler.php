<?php

namespace App\Model\User\UseCase\Opt\Edit;

use App\Model\Flusher;
use App\Model\User\Entity\Opt\OptRepository;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(OptRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $opt = $this->repository->get($command->optID);

        $opt->update($command->name);

        $this->flusher->flush();
    }
}
