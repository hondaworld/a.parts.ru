<?php

namespace App\Model\Income\UseCase\Income\Price;

use App\Model\Flusher;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\ReadModel\Provider\PriceUploaderFetcher;
use App\Service\Price\PartPriceService;

class Handler
{
    private $repository;
    private $flusher;
    private PartPriceService $partPriceService;
    private PriceUploaderFetcher $priceUploaderFetcher;

    public function __construct(
        IncomeRepository     $repository,
        PartPriceService     $partPriceService,
        PriceUploaderFetcher $priceUploaderFetcher,
        Flusher              $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->partPriceService = $partPriceService;
        $this->priceUploaderFetcher = $priceUploaderFetcher;
    }

    public function handle(Command $command): void
    {
        $income = $this->repository->get($command->incomeID);

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
            $command->priceZak
        );

        $command->priceDost = $prices['priceDostUsd'];
        $command->price = $prices['priceWithDostRub'];

        $income->updatePrices(
            $command->priceZak,
            $command->priceDost,
            $command->price
        );

        $this->priceUploaderFetcher->updatePrice(
            $income->getZapCard()->getCreater()->getTableName(),
            $income->getZapCard()->getNumber()->getValue(),
            $income->getZapCard()->getCreater()->getId(),
            $income->getProviderPrice()->getId(),
            $command->priceZak
        );

        $this->flusher->flush();
    }
}
