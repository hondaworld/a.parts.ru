<?php

namespace App\Model\Detail\UseCase\Weight\Edit;

use App\Model\Detail\Entity\Weight\WeightRepository;
use App\Model\Flusher;

class Handler
{
    private $flusher;
    private $weightRepository;

    public function __construct(WeightRepository $weightRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->weightRepository = $weightRepository;
    }

    public function handle(Command $command): void
    {
        $weight = $this->weightRepository->get($command->weightID);

        $weight->update(
            $command->weight,
            $command->weightIsReal
        );

        $this->flusher->flush();
    }
}
