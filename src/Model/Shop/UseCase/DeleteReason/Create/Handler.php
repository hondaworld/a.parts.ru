<?php

namespace App\Model\Shop\UseCase\DeleteReason\Create;

use App\Model\Flusher;
use App\Model\Shop\Entity\DeleteReason\DeleteReason;
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

        $deleteReason = new DeleteReason(
            $command->name,
            $command->isMain
        );

        $this->repository->add($deleteReason);

        $this->flusher->flush();
    }
}
