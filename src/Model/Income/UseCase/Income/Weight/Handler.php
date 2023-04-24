<?php

namespace App\Model\Income\UseCase\Income\Weight;

use App\Model\Detail\Entity\Weight\Weight;
use App\Model\Detail\Entity\Weight\WeightRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Service\Price\PartPriceService;

class Handler
{
    private Flusher $flusher;
    private WeightRepository $repository;
    private PartPriceService $partPriceService;
    private IncomeRepository $incomeRepository;

    public function __construct(
        WeightRepository $repository,
        PartPriceService $partPriceService,
        IncomeRepository $incomeRepository,
        Flusher $flusher
    )
    {
        $this->flusher = $flusher;
        $this->repository = $repository;
        $this->partPriceService = $partPriceService;
        $this->incomeRepository = $incomeRepository;
    }

    public function handle(Command $command, Income $income): void
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

        $incomes = $this->incomeRepository->findByProviderIncomeInWarehouse($income->getProviderPrice()->getProvider());
        foreach ($incomes as $income) {
            if ($command->number->isEqual($income->getZapCard()->getNumber()) && $command->creater->getId() == $income->getZapCard()->getCreater()->getId()) {
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

                $command->incomes[$income->getId()] = [
                    'priceDost' => $prices['priceDostUsd'],
                    'price' => $prices['priceWithDostRub']
                ];
            }
        }
        $this->flusher->flush();
    }
}
