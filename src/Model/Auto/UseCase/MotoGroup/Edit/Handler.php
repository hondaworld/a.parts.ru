<?php

namespace App\Model\Auto\UseCase\MotoGroup\Edit;

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
        $motoGroup = $this->repository->get($command->moto_groupID);

        $motoGroup->update($command->name, $command->photo);

        $this->flusher->flush();
    }
}
