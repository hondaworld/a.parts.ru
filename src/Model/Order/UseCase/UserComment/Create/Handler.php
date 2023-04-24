<?php

namespace App\Model\Order\UseCase\UserComment\Create;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;

class Handler
{
    private $flusher;

    public function __construct(Flusher $flusher)
    {
        $this->flusher = $flusher;
    }

    public function handle(Command $command, User $user, Manager $manager): void
    {
        $user->assignUserComment($manager, $command->comment);
        $this->flusher->flush();
    }
}
