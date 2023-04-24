<?php

namespace App\Model\Auto\UseCase\MotoGroup\Create;

use App\Model\Auto\Entity\MotoGroup\MotoGroup;
use App\Model\Auto\Entity\MotoGroup\MotoGroupRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(MotoGroupRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $motoGroup = new MotoGroup($command->name, $command->photo);

        $this->repository->add($motoGroup);

        $this->flusher->flush();
    }
}
