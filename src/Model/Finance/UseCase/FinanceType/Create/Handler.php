<?php

namespace App\Model\Finance\UseCase\FinanceType\Create;

use App\Model\Finance\Entity\FinanceType\FinanceType;
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
        if ($command->isMain) {
            $this->financeTypeRepository->updateMain();
        }

        $financeType = new FinanceType($command->name, $this->firmRepository->get($command->firmID), $command->isMain);

        $this->financeTypeRepository->add($financeType);

        $this->flusher->flush();
    }
}
