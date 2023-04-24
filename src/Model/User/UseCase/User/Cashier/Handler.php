<?php

namespace App\Model\User\UseCase\User\Cashier;

use App\Model\Beznal\Entity\Beznal\BeznalRepository;
use App\Model\Contact\Entity\Contact\ContactRepository;
use App\Model\Flusher;
use App\Model\User\Entity\User\UserRepository;

class Handler
{
    private $flusher;
    private $userRepository;
    private $contactRepository;
    private $beznalRepository;

    public function __construct(UserRepository $userRepository, ContactRepository $contactRepository, BeznalRepository $beznalRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->userRepository = $userRepository;
        $this->contactRepository = $contactRepository;
        $this->beznalRepository = $beznalRepository;
    }

    public function handle(Command $command): void
    {
        $user = $this->userRepository->get($command->userID);

        $user->updateCashier(
            $command->user->id ? $this->userRepository->get($command->user->id) : null,
            $command->user->contactID ? $this->contactRepository->get($command->user->contactID) : null,
            $command->user->beznalID ? $this->beznalRepository->get($command->user->beznalID) : null,
        );

        $this->flusher->flush();
    }
}
