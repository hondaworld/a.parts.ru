<?php

namespace App\Model\Income\UseCase\Income\Create;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Income\Entity\Status\IncomeStatusRepository;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\Service\Price\PartPriceService;

class Handler
{
    private $repository;
    private $flusher;
    private CreaterRepository $createrRepository;
    private ZapCardRepository $zapCardRepository;
    private ProviderPriceRepository $providerPriceRepository;
    private PartPriceService $partPriceService;
    private IncomeStatusRepository $incomeStatusRepository;

    public function __construct(
        IncomeRepository $repository,
        CreaterRepository $createrRepository,
        ZapCardRepository $zapCardRepository,
        ProviderPriceRepository $providerPriceRepository,
        PartPriceService $partPriceService,
        IncomeStatusRepository $incomeStatusRepository,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
        $this->zapCardRepository = $zapCardRepository;
        $this->partPriceService = $partPriceService;
        $this->providerPriceRepository = $providerPriceRepository;
        $this->incomeStatusRepository = $incomeStatusRepository;
    }

    public function handle(Command $command): void
    {
        $number = new DetailNumber($command->number);
        $creater = $this->createrRepository->get($command->createrID);
        $providerPrice = $this->providerPriceRepository->get($command->providerPriceID);

        $zapCard = $this->zapCardRepository->getOrCreate($number, $creater);

        $prices = $this->partPriceService->onePrice($number, $creater, $providerPrice);

        $income = new Income(
            $providerPrice,
            $this->incomeStatusRepository->get(IncomeStatus::DEFAULT_STATUS),
            $zapCard,
            $command->quantity,
            $prices['priceZak'],
            $prices['priceDostUsd'],
            $prices['priceWithDostRub']
        );

        $this->repository->add($income);

        $this->flusher->flush();
    }
}
