<?php

namespace App\Model\Shop\UseCase\DeleteReason\Edit;

use App\Model\Flusher;
use App\Model\Shop\Entity\DeleteReason\DeleteReasonRepository;

class Handler
{
    private DeleteReasonRepository $repository;
    private Flusher $flusher;

    public function __construct(DeleteReasonRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        if ($command->isMain) {
            $this->repository->updateMain();
        }

        $deleteReason = $this->repository->get($command->deleteReasonID);

        $deleteReason->update(
            $command->name,
            $command->isMain
        );

        $this->flusher->flush();
    }
}
