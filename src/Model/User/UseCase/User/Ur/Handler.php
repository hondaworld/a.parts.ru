<?php

namespace App\Model\User\UseCase\User\Ur;

use App\Model\Contact\Entity\Town\TownRepository;
use App\Model\Flusher;
use App\Model\User\Entity\User\Name;
use App\Model\User\Entity\User\Ur;
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

        $ur = new Ur(
            $command->organization,
            $command->inn,
            $command->kpp,
            $command->okpo,
            $command->ogrn,
            $command->isNDS,
            $command->isUr,
            $command->dogovor_num,
            $command->dogovor_date
        );

        $user->updateUr($command->name ?: $user->generateName(), $ur);

        $this->flusher->flush();
    }
}
