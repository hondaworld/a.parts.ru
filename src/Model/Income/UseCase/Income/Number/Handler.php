<?php

namespace App\Model\Income\UseCase\Income\Number;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Entity\Location\ZapSkladLocationRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\Service\Price\PartPriceService;

class Handler
{
    private $repository;
    private $flusher;
    private ZapCardRepository $zapCardRepository;
    private PartPriceService $partPriceService;
    private ZapSkladLocationRepository $zapSkladLocationRepository;
    private ZapSkladRepository $zapSkladRepository;

    public function __construct(
        IncomeRepository $repository,
        ZapCardRepository $zapCardRepository,
        PartPriceService $partPriceService,
        ZapSkladLocationRepository $zapSkladLocationRepository,
        ZapSkladRepository $zapSkladRepository,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->zapCardRepository = $zapCardRepository;
        $this->partPriceService = $partPriceService;
        $this->zapSkladLocationRepository = $zapSkladLocationRepository;
        $this->zapSkladRepository = $zapSkladRepository;
    }

    public function handle(Command $command): void
    {
        $income = $this->repository->get($command->incomeID);
        $number = new DetailNumber($command->number);

        $zapCard = $this->zapCardRepository->getOrCreate($number, $income->getZapCard()->getCreater());
        $zapCard->assignLocation($this->zapSkladRepository->get(ZapSklad::OSN_SKLAD_ID));
        $prices = $this->partPriceService->onePrice($number, $income->getZapCard()->getCreater(), $income->getProviderPrice());

        $income->updateZapCard($zapCard);
        $income->updatePrices($prices['priceZak'], $prices['priceDostUsd'], $prices['priceWithDostRub']);

        $this->flusher->flush();
    }
}
