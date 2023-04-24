<?php

namespace App\Model\Provider\UseCase\Provider\BalanceHistory\Edit;

use App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistoryRepository;
use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Flusher;

class Handler
{
    private FirmBalanceHistoryRepository $firmBalanceHistoryRepository;
    private Flusher $flusher;
    private FirmRepository $firmRepository;

    public function __construct(FirmBalanceHistoryRepository $firmBalanceHistoryRepository, FirmRepository $firmRepository, Flusher $flusher)
    {
        $this->firmBalanceHistoryRepository = $firmBalanceHistoryRepository;
        $this->flusher = $flusher;
        $this->firmRepository = $firmRepository;
    }

    public function handle(Command $command): void
    {
        $firmBalanceHistory = $this->firmBalanceHistoryRepository->get($command->balanceID);
        $firm = $this->firmRepository->get($command->firmID);

        $firmBalanceHistory->updateFirm($firm);
        $firmBalanceHistory->updateBalance($command->balance, $command->description ?: '');
        $firmBalanceHistory->updateBalanceNds($firm->isNDS() ?($command->balance_nds ?: 0) : 0);

        $this->flusher->flush();
    }
}
