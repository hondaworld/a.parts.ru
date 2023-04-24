<?php

namespace App\Model\Order\UseCase\Good\DateOfDelivery;

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
        $user->updateDateOfDelivery($command->dateofdelivery);
        if ($command->dateofdelivery) {
            $manager->assignOrderOperation($user, null, "Установка даты доставки " . $command->dateofdelivery->format('d.m.Y'));
        }
        $this->flusher->flush();
    }
}
