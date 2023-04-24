<?php

namespace App\Model\Income\UseCase\Income\PriceSelected;

use App\Model\Flusher;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Service\Price\PartPriceService;
use DomainException;

class Handler
{
    private $repository;
    private $flusher;
    private PartPriceService $partPriceService;

    public function __construct(
        IncomeRepository $repository,
        PartPriceService $partPriceService,
        Flusher          $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->partPriceService = $partPriceService;
    }

    public function handle(Command $command): array
    {

        $messages = [];

        foreach ($command->cols as $incomeID) {

            try {
                $income = $this->repository->get($incomeID);

                if ($income->getStatus()->isDeleted()) {
                    throw new \DomainException('Статус не должен быть удаленным');
                }

                if ($income->getIncomeDocument()) {
                    throw new \DomainException('Деталь ' . $income->getZapCard()->getNumber()->getValue() . ' уже оприходована');
                }

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

            } catch (DomainException $exception) {
                $messages[] = ['type' => 'danger', 'message' => $exception->getMessage()];
            }
        }


        $this->flusher->flush();

        return $messages;
    }
}
