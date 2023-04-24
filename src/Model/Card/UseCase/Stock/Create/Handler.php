<?php

namespace App\Model\Card\UseCase\Stock\Create;

use App\Model\Card\Entity\Stock\ZapCardStock;
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
        $zapCardStock = new ZapCardStock($command->name, $command->text);

        foreach ($command->providers as $providerID) {
            $provider = $this->providerRepository->get($providerID);
            $zapCardStock->assignProvider($provider);
        }

        $this->zapCardStockRepository->add($zapCardStock);

        $this->flusher->flush();
    }
}
