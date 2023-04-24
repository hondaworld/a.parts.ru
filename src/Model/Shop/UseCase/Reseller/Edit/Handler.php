<?php

namespace App\Model\Shop\UseCase\Reseller\Edit;

use App\Model\Flusher;
use App\Model\Shop\Entity\Reseller\ResellerRepository;

class Handler
{
    private ResellerRepository $repository;
    private Flusher $flusher;

    public function __construct(ResellerRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $deliveryTk = $this->repository->get($command->resellerID);

        $deliveryTk->update(
            $command->name
        );

        $this->flusher->flush();
    }
}
