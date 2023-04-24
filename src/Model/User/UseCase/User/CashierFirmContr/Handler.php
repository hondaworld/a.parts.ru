<?php

namespace App\Model\User\UseCase\User\CashierFirmContr;

use App\Model\Flusher;
use App\Model\User\Entity\FirmContr\FirmContrRepository;
use App\Model\User\Entity\User\UserRepository;

class Handler
{
    private $users;
    private $firmContrRepository;
    private $flusher;

    public function __construct(UserRepository $users, FirmContrRepository $firmContrRepository, Flusher $flusher)
    {
        $this->users = $users;
        $this->firmContrRepository = $firmContrRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $user = $this->users->get($command->userID);

        $user->updateCashierFirmContr(
            $command->firmcontrID ? $this->firmContrRepository->get($command->firmcontrID) : null
        );

        $this->flusher->flush();
    }
}
