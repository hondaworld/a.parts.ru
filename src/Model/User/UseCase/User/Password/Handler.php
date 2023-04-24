<?php

namespace App\Model\User\UseCase\User\Password;

use App\Model\Flusher;
use App\Model\User\Entity\User\UserRepository;
use App\Model\User\Service\PasswordHasher;

class Handler
{
    private $users;
    private $flusher;
    private $hasher;

    public function __construct(UserRepository $users, Flusher $flusher, PasswordHasher $hasher)
    {
        $this->users = $users;
        $this->flusher = $flusher;
        $this->hasher = $hasher;
    }

    public function handle(Command $command): void
    {
        $user = $this->users->get($command->userID);

        $user->updatePassword($this->hasher->hash($command->password));

        $this->flusher->flush();
    }
}
