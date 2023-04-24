<?php

namespace App\Model\User\UseCase\User\Create;

use App\Model\Contact\Entity\Town\TownRepository;
use App\Model\Flusher;
use App\Model\User\Entity\Opt\OptRepository;
use App\Model\User\Entity\User\Name;
use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\UserRepository;

class Handler
{
    private $users;
    private $flusher;
    private $opts;
    private $towns;

    public function __construct(UserRepository $users, OptRepository $opts, TownRepository $towns, Flusher $flusher)
    {
        $this->users = $users;
        $this->flusher = $flusher;
        $this->opts = $opts;
        $this->towns = $towns;
    }

    public function handle(Command $command): User
    {
        if ($this->users->hasByPhoneMobile($command->phonemob->getValue())) {
            throw new \DomainException('Клиент с таким мобильным телефоном уже есть.');
        }

        $name = new Name(
            $command->firstname,
            $command->lastname,
            $command->middlename
        );

        $user = new User(
            $this->opts->get($command->optID),
            $command->phonemob->getValue(),
            $name,
            $name->generateName(),
            $command->town->id ? $this->towns->get($command->town->id) : null,
        );

//        dump($user);

        $this->users->add($user);

        $this->flusher->flush();

        return $user;
    }
}
