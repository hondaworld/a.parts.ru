<?php

namespace App\Model\Income\UseCase\Document\Weight;

use App\Model\Detail\Entity\Weight\Weight;
use App\Model\Detail\Entity\Weight\WeightRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Provider\Entity\Provider\Provider;
use App\Service\Price\PartPriceService;

class Handler
{
    private $flusher;
    private WeightRepository $repository;
    private PartPriceService $partPriceService;
    private IncomeRepository $incomeRepository;

    public function __construct(
        WeightRepository $repository,
        PartPriceService $partPriceService,
        IncomeRepository $incomeRepository,
        Flusher          $flusher
    )
    {
        $this->flusher = $flusher;
        $this->repository = $repository;
        $this->partPriceService = $partPriceService;
        $this->incomeRepository = $incomeRepository;
    }

    public function handle(Command $command, Provider $provider): void
    {
        $weight = $this->repository->findByNumberAndCreater($command->number, $command->creater);
        if ($weight) {
            $weight->update($command->weight, $command->weightIsReal);
        } else {
            $weight = new Weight(
                $command->number,
                $command->creater,
                $command->weight,
                $command->weightIsReal
            );
            $this->repository->add($weight);
        }
        $this->flusher->flush();

        $incomes = $this->incomeRepository->findByProviderIncomeInWarehouse($provider);
        foreach ($incomes as $income) {
            if ($command->number->isEqual($income->getZapCard()->getNumber()) && $command->creater->isEqual($income->getZapCard()->getCreater())) {
                $prices = $this->partPriceService->onePriceWithPriceZak(
                    $income->getZapCard()->getNumber(),
                    $income->getZapCard()->getCreater(),
                    $income->getProviderPrice(),
                    $income->getPriceZak()
                );

                $income->updatePrices(
                    $income->getPriceZak(),
                    $prices['priceDostUsd'],
                    $prices['priceWithDostRub']
                );
            }
        }
        $this->flusher->flush();
    }
}
