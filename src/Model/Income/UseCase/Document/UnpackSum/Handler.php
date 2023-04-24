<?php

namespace App\Model\Income\UseCase\Document\UnpackSum;

use App\Model\Provider\Entity\Provider\Provider;
use App\ReadModel\Income\IncomeFetcher;
use Doctrine\DBAL\Exception;

class Handler
{
    private IncomeFetcher $incomeFetcher;

    public function __construct(IncomeFetcher $incomeFetcher)
    {
        $this->incomeFetcher = $incomeFetcher;
    }

    public function handle(Command $command, Provider $provider): void
    {
        $sum = $this->incomeFetcher->getSumUnPackIncomeInWarehouse($provider->getId());
        if (!$command->sumIsEqual($sum)) {
            throw new \DomainException('Сумма не совпадает ' . $sum);
        }

        try {
            $this->incomeFetcher->updateSumDoneUnPackIncomeInWarehouse($provider->getId());
        } catch (Exception $e) {
            throw new \DomainException($e->getMessage());
        }
    }
}
