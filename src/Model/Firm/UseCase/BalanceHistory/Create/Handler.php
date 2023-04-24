<?php

namespace App\Model\Firm\UseCase\BalanceHistory\Create;

use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Provider\Entity\Provider\ProviderRepository;

class Handler
{
    private Flusher $flusher;
    private ProviderRepository $providerRepository;

    public function __construct(
        ProviderRepository           $providerRepository,
        Flusher                      $flusher
    )
    {
        $this->flusher = $flusher;
        $this->providerRepository = $providerRepository;
    }

    public function handle(Command $command, Firm $firm, Manager $manager): void
    {
        $provider = $this->providerRepository->get($command->providerID);
        $firm->assignFirmBalanceHistory($provider, $command->balance, $firm->isNDS() ? $command->balance_nds : 0, $manager, $command->description, null);
        $this->flusher->flush();
    }
}
