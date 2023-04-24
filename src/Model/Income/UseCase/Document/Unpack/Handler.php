<?php

namespace App\Model\Income\UseCase\Document\Unpack;

use App\ReadModel\Income\IncomeFetcher;

class Handler
{
    private IncomeFetcher $incomeFetcher;

    public function __construct(IncomeFetcher $incomeFetcher)
    {
        $this->incomeFetcher = $incomeFetcher;
    }

    public function handle(Command $command): bool
    {
        return !$this->incomeFetcher->isExistNotSumDoneUnPackIncomeInWarehouse($command->providerID);
    }
}
