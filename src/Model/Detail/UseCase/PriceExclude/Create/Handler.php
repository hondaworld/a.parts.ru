<?php

namespace App\Model\Detail\UseCase\PriceExclude\Create;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Detail\Entity\PriceExclude\DetailProviderPriceExclude;
use App\Model\Detail\Entity\PriceExclude\DetailProviderPriceExcludeRepository;
use App\Model\Flusher;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\ReadModel\Detail\DetailProviderPriceExcludeFetcher;

class Handler
{
    private $repository;
    private $flusher;
    private CreaterRepository $createrRepository;
    private DetailProviderPriceExcludeFetcher $detailProviderPriceExcludeFetcher;
    /**
     * @var ProviderPriceRepository
     */
    private ProviderPriceRepository $providerPriceRepository;

    public function __construct(
        DetailProviderPriceExcludeRepository $repository,
        CreaterRepository $createrRepository,
        DetailProviderPriceExcludeFetcher $detailProviderPriceExcludeFetcher,
        ProviderPriceRepository $providerPriceRepository,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
        $this->detailProviderPriceExcludeFetcher = $detailProviderPriceExcludeFetcher;
        $this->providerPriceRepository = $providerPriceRepository;
    }

    public function handle(Command $command): void
    {
        $number = new DetailNumber($command->number);

        if (!$this->detailProviderPriceExcludeFetcher->hasProviderPriceExclude(
                $number->getValue(),
                $command->createrID,
                $command->providerPriceID
            )
        ) {
            $detailProviderPriceExclude = new DetailProviderPriceExclude(
                $number,
                $this->createrRepository->get($command->createrID),
                $this->providerPriceRepository->get($command->providerPriceID)
            );

            $this->repository->add($detailProviderPriceExclude);
        }

        $this->flusher->flush();
    }
}
