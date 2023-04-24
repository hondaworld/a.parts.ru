<?php

namespace App\Model\Card\UseCase\Inventarization\Quantity;

use App\Model\Card\Entity\Inventarization\InventarizationGoodRepository;
use App\Model\Flusher;

class Handler
{
    private InventarizationGoodRepository $inventarizationGoodRepository;
    private Flusher $flusher;

    public function __construct(
        InventarizationGoodRepository $inventarizationGoodRepository,
        Flusher                       $flusher
    )
    {
        $this->inventarizationGoodRepository = $inventarizationGoodRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $inventarizationGood = $this->inventarizationGoodRepository->get($command->goodID);
        $inventarizationGood->updateQuantityReal($command->quantity_real);
        $this->flusher->flush();
    }
}
