<?php

namespace App\Model\User\UseCase\User\Manager;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\User\Entity\User\UserRepository;

class Handler
{
    private $users;
    private $flusher;
    private $managers;

    public function __construct(UserRepository $users, managerRepository $managers, Flusher $flusher)
    {
        $this->users = $users;
        $this->flusher = $flusher;
        $this->managers = $managers;
    }

    public function handle(Command $command): void
    {
        $user = $this->users->get($command->userID);

        $user->updateOwner(
            $command->managerID ? $this->managers->get($command->managerID) : null
        );

        $this->flusher->flush();
    }
}
