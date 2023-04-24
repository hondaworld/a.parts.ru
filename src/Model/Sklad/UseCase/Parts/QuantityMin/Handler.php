<?php

namespace App\Model\Sklad\UseCase\Parts\QuantityMin;

use App\Model\Card\Entity\Location\ZapSkladLocationRepository;
use App\Model\Flusher;

class Handler
{
    private ZapSkladLocationRepository $repository;
    private Flusher $flusher;

    public function __construct(
        ZapSkladLocationRepository $repository,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $zapSkladLocation = $this->repository->get($command->zapSkladLocationID);
        $zapSkladLocation->updateQuantityMin($command->quantityMin);
        $this->flusher->flush();
    }
}
