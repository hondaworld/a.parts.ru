<?php

namespace App\Model\User\UseCase\User\PhoneMobile;

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

        if ($this->users->hasByPhoneMobile($command->phonemob->getValue(), $command->userID)) {
            throw new \DomainException('Клиент с таким мобильным телефоном уже есть.');
        }

        $user->updatePhoneMobile($command->phonemob->getValue(), $command->isSms);

        $this->flusher->flush();
    }
}
