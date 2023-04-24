<?php

namespace App\Model\User\UseCase\BalanceHistory\Attach;

use App\Model\Flusher;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistoryRepository;

class Handler
{
    private UserBalanceHistoryRepository $userBalanceHistoryRepository;
    private Flusher $flusher;

    public function __construct(UserBalanceHistoryRepository $userBalanceHistoryRepository, Flusher $flusher)
    {
        $this->userBalanceHistoryRepository = $userBalanceHistoryRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $userBalanceHistory = $this->userBalanceHistoryRepository->get($command->balanceID);

        $userBalanceHistory->updateAttach($command->attach);

        $this->flusher->flush();
    }
}
