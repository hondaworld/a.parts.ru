<?php

namespace App\Model\Finance\UseCase\FinanceType\Edit;

use App\Model\Finance\Entity\FinanceType\FinanceTypeRepository;
use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Flusher;

class Handler
{
    private $financeTypeRepository;
    private $flusher;
    private $firmRepository;

    public function __construct(FinanceTypeRepository $financeTypeRepository, FirmRepository $firmRepository, Flusher $flusher)
    {
        $this->financeTypeRepository = $financeTypeRepository;
        $this->flusher = $flusher;
        $this->firmRepository = $firmRepository;
    }

    public function handle(Command $command): void
    {

        $financeType = $this->financeTypeRepository->get($command->finance_typeID);

        if (!$financeType->isMain() && $command->isMain) {
            $this->financeTypeRepository->updateMain();
        }

        $financeType->update($command->name, $this->firmRepository->get($command->firmID), $command->isMain);

        $this->flusher->flush();
    }
}
