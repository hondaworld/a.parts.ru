<?php

namespace App\Model\Provider\UseCase\Provider\Edit;

use App\Model\Flusher;
use App\Model\Provider\Entity\Provider\ProviderRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\Model\User\Entity\User\UserRepository;

class Handler
{
    private $providerRepository;
    private $flusher;
    private $userRepository;
    private $zapSkladRepository;

    public function __construct(ProviderRepository $providerRepository, UserRepository $userRepository, ZapSkladRepository $zapSkladRepository, Flusher $flusher)
    {
        $this->providerRepository = $providerRepository;
        $this->flusher = $flusher;
        $this->userRepository = $userRepository;
        $this->zapSkladRepository = $zapSkladRepository;
    }

    public function handle(Command $command): void
    {
        $provider = $this->providerRepository->get($command->providerID);

        $provider->update(
            $command->name,
            $this->userRepository->get($command->user->id),
            $this->zapSkladRepository->get($command->zapSkladID),
            $command->koef_dealer,
            $command->isDealer
        );

        $this->flusher->flush();
    }
}
