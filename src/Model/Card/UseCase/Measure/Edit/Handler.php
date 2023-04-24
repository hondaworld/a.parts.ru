<?php

namespace App\Model\Card\UseCase\Measure\Edit;

use App\Model\Card\Entity\Measure\EdIzmRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(EdIzmRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $edIzm = $this->repository->get($command->ed_izmID);

        $edIzm->update($command->name, $command->name_short, $command->okei);

        $this->flusher->flush();
    }
}
