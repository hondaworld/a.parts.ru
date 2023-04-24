<?php

namespace App\Model\User\UseCase\User\ExcludeProviders;

use App\Model\Flusher;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Provider\Entity\Provider\ProviderRepository;
use App\Model\User\Entity\User\UserRepository;

class Handler
{
    private $users;
    private $flusher;
    private $providers;

    public function __construct(UserRepository $users, ProviderRepository $providers, Flusher $flusher)
    {
        $this->users = $users;
        $this->flusher = $flusher;
        $this->providers = $providers;
    }

    public function handle(Command $command): void
    {
        $user = $this->users->get($command->userID);

        $providers = array_map(function (int $id): Provider {
            return $this->providers->get($id);
        }, $command->providers);

        $user->updateExcludeProvider($providers);
        $this->flusher->flush();
    }
}
