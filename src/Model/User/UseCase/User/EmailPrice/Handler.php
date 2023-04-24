<?php

namespace App\Model\User\UseCase\User\EmailPrice;

use App\Model\Flusher;
use App\Model\User\Entity\User\EmailPrice;
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

        $emailPrice = new EmailPrice($command->email_price ?: '', $command->zapSkladID, $command->isPrice, $command->isPriceSummary);
        $user->updateEmailPrice($emailPrice);

        $this->flusher->flush();
    }
}
