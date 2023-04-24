<?php

namespace App\Model\User\UseCase\User\Dop;

use App\Model\Flusher;
use App\Model\User\Entity\User\UserRepository;

class Handler
{
    private $users;
    private $flusher;

    public function __construct(UserRepository $users, Flusher $flusher)
    {
        $this->users = $users;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $user = $this->users->get($command->userID);

        $user->updateDop($command->dateofuser, $command->sex ?: '');

        $this->flusher->flush();
    }
}
