<?php

namespace App\Model\Order\UseCase\Good\DateOfService;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;

class Handler
{
    private Flusher $flusher;

    public function __construct(
        Flusher $flusher
    )
    {
        $this->flusher = $flusher;
    }

    public function handle(Command $command, User $user, Manager $manager): void
    {
        $user->updateDateOfService($command->dateofservice);
        if ($command->dateofservice) {
            $manager->assignOrderOperation($user, null, "Установка даты сервиса " . $command->dateofservice->format('d.m.Y'));
        }
        $this->flusher->flush();
    }
}
