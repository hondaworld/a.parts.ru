<?php

namespace App\Model\User\UseCase\User\Discount;

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

        $user->updateDiscount($command->schetDays, $command->discountParts, $command->discountService);
        $user->updateIsNotUpdateDiscount($command->is_not_update_discount);

        $this->flusher->flush();
    }
}
