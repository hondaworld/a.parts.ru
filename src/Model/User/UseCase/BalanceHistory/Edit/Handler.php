<?php

namespace App\Model\User\UseCase\BalanceHistory\Edit;

use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Flusher;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistoryRepository;

class Handler
{
    private $userBalanceHistoryRepository;
    private $firmRepository;
    private $flusher;

    public function __construct(UserBalanceHistoryRepository $userBalanceHistoryRepository, FirmRepository $firmRepository, Flusher $flusher)
    {
        $this->userBalanceHistoryRepository = $userBalanceHistoryRepository;
        $this->firmRepository = $firmRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $userBalanceHistory = $this->userBalanceHistoryRepository->get($command->balanceID);

        $userBalanceHistory->update($command->balance, $this->firmRepository->get($command->firmID), $command->description ?: '');

        $this->flusher->flush();
    }
}
