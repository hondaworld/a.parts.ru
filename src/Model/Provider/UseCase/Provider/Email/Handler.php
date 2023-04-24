<?php

namespace App\Model\Provider\UseCase\Provider\Email;

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

        $provider->updateEmail(
            $command->incomeOrderNumber,
            $command->incomeOrderSubject,
            $command->incomeOrderText,
            $command->incomeOrderSubject5,
            $command->incomeOrderText5,
            $command->incomeOrderEmail,
            $command->isIncomeOrder
        );

        $this->flusher->flush();
    }
}
