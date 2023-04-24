<?php

namespace App\Model\Provider\UseCase\Provider\Create;

use App\Model\Flusher;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Provider\Entity\Provider\ProviderRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\Model\User\Entity\User\UserRepository;

class Handler
{
    private ProviderRepository $providerRepository;
    private Flusher $flusher;
    private UserRepository $userRepository;
    private ZapSkladRepository $zapSkladRepository;

    public function __construct(ProviderRepository $providerRepository, UserRepository $userRepository, ZapSkladRepository $zapSkladRepository, Flusher $flusher)
    {
        $this->providerRepository = $providerRepository;
        $this->flusher = $flusher;
        $this->userRepository = $userRepository;
        $this->zapSkladRepository = $zapSkladRepository;
    }

    public function handle(Command $command): Provider
    {
        $provider = new Provider(
            $command->name,
            $this->userRepository->get($command->user->id),
            $this->zapSkladRepository->get($command->zapSkladID),
            $command->koef_dealer,
            $command->isDealer
        );

        $this->providerRepository->add($provider);

        $this->flusher->flush();

        return $provider;
    }
}
