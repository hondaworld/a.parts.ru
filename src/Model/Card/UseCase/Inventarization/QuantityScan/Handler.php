<?php

namespace App\Model\Card\UseCase\Inventarization\QuantityScan;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Entity\Inventarization\Inventarization;
use App\Model\Card\Entity\Inventarization\InventarizationGood;
use App\Model\Card\Entity\Inventarization\InventarizationGoodRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;

class Handler
{
    private Flusher $flusher;
    private InventarizationGoodRepository $inventarizationGoodRepository;
    private ZapCardRepository $zapCardRepository;

    public function __construct(
        Flusher                       $flusher,
        InventarizationGoodRepository $inventarizationGoodRepository,
        ZapCardRepository             $zapCardRepository
    )
    {
        $this->flusher = $flusher;
        $this->inventarizationGoodRepository = $inventarizationGoodRepository;
        $this->zapCardRepository = $zapCardRepository;
    }

    public function handle(Command $command, Inventarization $inventarization, ZapSklad $zapSklad, array $good, Manager $manager): void
    {
        $zapCard = $this->zapCardRepository->get($good['zapCardID']);
        $inventarizationGood = $this->inventarizationGoodRepository->findByZapCardAndZapSklad($inventarization, $zapCard, $zapSklad);

        if ($inventarizationGood) {
            $inventarizationGood->assignQuantityReal($command->quantity_real);
        } else {
            $inventarizationGood = new InventarizationGood(
                $inventarization,
                $zapCard,
                $zapSklad,
                $good['quantity'],
                $good['reserve'],
                $command->quantity_real,
                $manager
            );
            $this->inventarizationGoodRepository->add($inventarizationGood);
        }

        $this->flusher->flush();
    }
}
