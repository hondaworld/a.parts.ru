<?php

namespace App\Model\Card\UseCase\Card\PriceService;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(
        ZapCardRepository $repository,
        Flusher           $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $zapCard = $this->repository->get($command->zapCardID);

        $zapCard->updatePriceService($command->price_service);

        $this->flusher->flush();
    }
}
