<?php

namespace App\Model\Card\UseCase\Stock\Edit;

use App\Model\Card\Entity\Stock\ZapCardStockRepository;
use App\Model\Flusher;
use App\Model\Provider\Entity\Provider\ProviderRepository;

class Handler
{
    private $zapCardStockRepository;
    private $flusher;
    private $providerRepository;

    public function __construct(ZapCardStockRepository $zapCardStockRepository, ProviderRepository $providerRepository, Flusher $flusher)
    {
        $this->zapCardStockRepository = $zapCardStockRepository;
        $this->flusher = $flusher;
        $this->providerRepository = $providerRepository;
    }

    public function handle(Command $command): void
    {
        $stock = $this->zapCardStockRepository->get($command->stockID);

        $stock->update(
            $command->name,
            $command->text,
            $command->dateofadded
        );

        $stock->cleaProviders();
        foreach ($command->providers as $providerID) {
            $provider = $this->providerRepository->get($providerID);
            $stock->assignProvider($provider);
        }

        $this->flusher->flush();
    }
}
