<?php

namespace App\Model\Card\UseCase\Measure\Create;

use App\Model\Card\Entity\Measure\EdIzm;
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
        $edIzm = new EdIzm($command->name, $command->name_short, $command->okei);

        $this->repository->add($edIzm);

        $this->flusher->flush();
    }
}
