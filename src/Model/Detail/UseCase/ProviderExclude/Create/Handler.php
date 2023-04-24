<?php

namespace App\Model\Detail\UseCase\ProviderExclude\Create;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Detail\Entity\ProviderExclude\DetailProviderExclude;
use App\Model\Detail\Entity\ProviderExclude\DetailProviderExcludeRepository;
use App\Model\Flusher;
use App\ReadModel\Detail\DetailProviderExcludeFetcher;

class Handler
{
    private $repository;
    private $flusher;
    private CreaterRepository $createrRepository;
    private DetailProviderExcludeFetcher $detailProviderExcludeFetcher;

    public function __construct(
        DetailProviderExcludeRepository $repository,
        CreaterRepository $createrRepository,
        DetailProviderExcludeFetcher $detailProviderExcludeFetcher,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
        $this->detailProviderExcludeFetcher = $detailProviderExcludeFetcher;
    }

    public function handle(Command $command): void
    {
        $number = new DetailNumber($command->number);
        $providerID = $command->providerID ?: -1;

        if (!$this->detailProviderExcludeFetcher->hasProviderExclude(
                $number->getValue(),
                $command->createrID,
            $providerID
            )
        ) {
            $detailProviderExclude = new DetailProviderExclude(
                $number,
                $this->createrRepository->get($command->createrID),
                $providerID,
                $command->comment
            );

            $this->repository->add($detailProviderExclude);
        }

        $this->flusher->flush();
    }
}
