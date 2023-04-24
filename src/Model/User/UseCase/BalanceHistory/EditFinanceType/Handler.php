<?php

namespace App\Model\User\UseCase\BalanceHistory\EditFinanceType;

use App\Model\Finance\Entity\FinanceType\FinanceTypeRepository;
use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Flusher;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistoryRepository;

class Handler
{
    private $userBalanceHistoryRepository;
    private $firmRepository;
    private $financeTypeRepository;
    private $flusher;

    public function __construct(UserBalanceHistoryRepository $userBalanceHistoryRepository, FirmRepository $firmRepository, FinanceTypeRepository $financeTypeRepository, Flusher $flusher)
    {
        $this->userBalanceHistoryRepository = $userBalanceHistoryRepository;
        $this->firmRepository = $firmRepository;
        $this->financeTypeRepository = $financeTypeRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $userBalanceHistory = $this->userBalanceHistoryRepository->get($command->balanceID);

        $userBalanceHistory->updateFinanceType($this->firmRepository->get($command->firmID), $this->financeTypeRepository->get($command->finance_typeID));

        $this->flusher->flush();
    }
}
