<?php

namespace App\Model\Firm\UseCase\BalanceHistory\Edit;

use App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistoryRepository;
use App\Model\Flusher;

class Handler
{
    private FirmBalanceHistoryRepository $firmBalanceHistoryRepository;
    private Flusher $flusher;

    public function __construct(FirmBalanceHistoryRepository $firmBalanceHistoryRepository, Flusher $flusher)
    {
        $this->firmBalanceHistoryRepository = $firmBalanceHistoryRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $firmBalanceHistory = $this->firmBalanceHistoryRepository->get($command->balanceID);

        $firmBalanceHistory->updateBalance($command->balance, $command->description);

        if ($firmBalanceHistory->getFirm()->isNDS()) {
            $firmBalanceHistory->updateBalanceNds($command->balance_nds);
        }

        $this->flusher->flush();
    }
}
