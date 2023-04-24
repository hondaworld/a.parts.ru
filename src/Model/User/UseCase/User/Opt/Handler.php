<?php

namespace App\Model\User\UseCase\User\Opt;

use App\Model\Flusher;
use App\Model\User\Entity\Opt\OptRepository;
use App\Model\User\Entity\ShopPayType\ShopPayTypeRepository;
use App\Model\User\Entity\User\UserRepository;

class Handler
{
    private $users;
    private $flusher;
    private $opts;
    private $payTypes;

    public function __construct(UserRepository $users, OptRepository $opts, ShopPayTypeRepository $payTypes, Flusher $flusher)
    {
        $this->users = $users;
        $this->flusher = $flusher;
        $this->opts = $opts;
        $this->payTypes = $payTypes;
    }

    public function handle(Command $command): void
    {
        $user = $this->users->get($command->userID);

        $user->updateOpt(
            $this->opts->get($command->optID),
            $command->shopPayTypeID ? $this->payTypes->get($command->shopPayTypeID) : null
        );

        $this->flusher->flush();
    }
}
