<?php

namespace App\Model\Card\UseCase\Inventarization\CreateGood;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Entity\Inventarization\Inventarization;
use App\Model\Card\Entity\Inventarization\InventarizationGood;
use App\Model\Card\Entity\Inventarization\InventarizationGoodRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\ReadModel\Income\IncomeSkladFetcher;

class Handler
{
    private InventarizationGoodRepository $inventarizationGoodRepository;
    private Flusher $flusher;
    private IncomeSkladFetcher $incomeSkladFetcher;
    private ZapCardRepository $zapCardRepository;
    private ZapSkladRepository $zapSkladRepository;

    public function __construct(
        InventarizationGoodRepository $inventarizationGoodRepository,
        ZapCardRepository             $zapCardRepository,
        ZapSkladRepository            $zapSkladRepository,
        IncomeSkladFetcher            $incomeSkladFetcher,
        Flusher                       $flusher
    )
    {
        $this->inventarizationGoodRepository = $inventarizationGoodRepository;
        $this->flusher = $flusher;
        $this->incomeSkladFetcher = $incomeSkladFetcher;
        $this->zapCardRepository = $zapCardRepository;
        $this->zapSkladRepository = $zapSkladRepository;
    }

    public function handle(Command $command, Inventarization $inventarization, Manager $manager): void
    {
        $sklads = $this->incomeSkladFetcher->findQuantityZapCardInAllSklads($command->zapCardID);
        $reserve = $sklads[$command->zapSkladID] ? $sklads[$command->zapSkladID]['reserve'] : 0;
        $quantity = $sklads[$command->zapSkladID] ? $sklads[$command->zapSkladID]['quantityIn'] : 0;

        $inventarizationGood = new InventarizationGood(
            $inventarization,
            $this->zapCardRepository->get($command->zapCardID),
            $this->zapSkladRepository->get($command->zapSkladID),
            $quantity,
            $reserve, $command->quantity_real,
            $manager
        );
        $this->inventarizationGoodRepository->add($inventarizationGood);
        $this->flusher->flush();
    }
}
