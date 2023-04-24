<?php

namespace App\Model\User\UseCase\User\Name;

use App\Model\Contact\Entity\Town\TownRepository;
use App\Model\Flusher;
use App\Model\User\Entity\User\Name;
use App\Model\User\Entity\User\UserRepository;

class Handler
{
    private $users;
    private $flusher;
    private $towns;

    public function __construct(UserRepository $users, TownRepository $towns, Flusher $flusher)
    {
        $this->users = $users;
        $this->flusher = $flusher;
        $this->towns = $towns;
    }

    public function handle(Command $command): void
    {
        $user = $this->users->get($command->userID);

        $user_name = new Name($command->firstname, $command->lastname, $command->middlename);
        $user->updateName(
            $user_name,
            $command->name ?: $user->generateName(),
            $command->town->id ? $this->towns->get($command->town->id) : null
        );

        $this->flusher->flush();
    }
}
