<?php

namespace App\Model\Card\UseCase\Inventarization\Edit;

use App\Model\Card\Entity\Inventarization\InventarizationRepository;
use App\Model\Flusher;

class Handler
{
    private $inventarizationRepository;
    private $flusher;

    public function __construct(InventarizationRepository $inventarizationRepository, Flusher $flusher)
    {
        $this->inventarizationRepository = $inventarizationRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $inventarization = $this->inventarizationRepository->get($command->inventarizationID);

        $inventarization->update($command->dateofadded);

        if ($command->isClose) {
            $inventarization->closed();
        }

        $this->flusher->flush();
    }
}
