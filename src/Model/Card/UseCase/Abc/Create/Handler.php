<?php

namespace App\Model\Card\UseCase\Abc\Create;

use App\Model\Card\Entity\Abc\Abc;
use App\Model\Card\Entity\Abc\AbcRepository;
use App\Model\Flusher;
use DomainException;

class Handler
{
    private AbcRepository $repository;
    private Flusher $flusher;

    public function __construct(AbcRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $command->abc = strtoupper($command->abc);

        if ($this->repository->hasByAbc($command->abc)) {
            throw new DomainException('Такая ABC уже есть.');
        }

        $abc = new Abc($command->abc, $command->description ?: '');

        $this->repository->add($abc);

        $this->flusher->flush();
    }
}
