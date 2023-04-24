<?php

namespace App\Model\Card\UseCase\Card\Description;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(
        ZapCardRepository $repository,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $zapCard = $this->repository->get($command->zapCardID);

        $zapCard->updateDescription(
            $command->text,
            $command->text_fake
        );

        $this->flusher->flush();
    }
}
