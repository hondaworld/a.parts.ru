<?php

namespace App\Model\Card\UseCase\Inventarization\Create;

use App\Model\Card\Entity\Inventarization\Inventarization;
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
        $inventarization = new Inventarization($command->dateofadded);
        $this->inventarizationRepository->add($inventarization);
        $this->flusher->flush();
    }
}
