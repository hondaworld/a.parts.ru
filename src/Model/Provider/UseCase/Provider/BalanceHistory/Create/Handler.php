<?php

namespace App\Model\Provider\UseCase\Provider\BalanceHistory\Create;

use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Provider\Entity\Provider\Provider;

class Handler
{
    private Flusher $flusher;
    private FirmRepository $firmRepository;

    public function __construct(
        FirmRepository               $firmRepository,
        Flusher                      $flusher
    )
    {
        $this->flusher = $flusher;
        $this->firmRepository = $firmRepository;
    }

    public function handle(Command $command, Provider $provider, Manager $manager): void
    {
        $firm = $this->firmRepository->get($command->firmID);
        $firm->assignFirmBalanceHistory($provider, -(str_replace(',', '.', $command->balance)), $firm->isNDS() ? ($command->balance_nds ?: 0) : 0, $manager, $command->description ?: '', null);
        $this->flusher->flush();
    }
}
