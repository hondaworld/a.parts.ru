<?php

namespace App\Model\Provider\UseCase\Provider\Send;

use App\Model\Flusher;
use App\Model\Provider\Entity\Provider\ProviderRepository;

class Handler
{
    private $providerRepository;
    private $flusher;

    public function __construct(ProviderRepository $providerRepository, Flusher $flusher)
    {
        $this->providerRepository = $providerRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $provider = $this->providerRepository->get($command->providerID);

        $provider->updateSend(
            $command->isIncomeOrderAutoSend,
            $command->incomeOrderWeekDays,
            $command->incomeOrderTime->format('H:i'),
        );

        $this->flusher->flush();
    }
}
