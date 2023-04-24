<?php

namespace App\Model\Order\UseCase\Order\Create;

use App\Model\Flusher;
use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\Opt\OptRepository;
use App\Model\User\Entity\User\Name;
use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\UserRepository;

class Handler
{
    private $users;
    private $flusher;
    private $opts;

    public function __construct(UserRepository $users, OptRepository $opts, Flusher $flusher)
    {
        $this->users = $users;
        $this->flusher = $flusher;
        $this->opts = $opts;
    }

    public function handle(Command $command): User
    {
        $user = $this->users->findByPhoneMobile($command->phonemob);

        $name = new Name(
            $command->firstname,
            $command->lastname,
            ''
        );

        if ($user) {
            $user->updateUserName($name);
        } else {
            $user = new User(
                $this->opts->get(Opt::DEFAULT_OPT_ID),
                $command->phonemob,
                $name,
                $name->generateName(),
                null,
            );
            $this->users->add($user);
        }

        $this->flusher->flush();
        return $user;
    }
}
