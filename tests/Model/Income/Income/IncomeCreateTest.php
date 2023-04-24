<?php

namespace App\Tests\Model\Income\Income;

use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Tests\Builder\Card\ZapCardBuilder;
use PHPUnit\Framework\TestCase;

class IncomeCreateTest extends TestCase
{
    public function testIncomeCreate(): void
    {
        $incomeStatus = $this->createMock(IncomeStatus::class);
        $incomeStatus->method('getId')->willReturn(IncomeStatus::DEFAULT_STATUS);

        $zapCard = (new ZapCardBuilder())->build();

        $providerPrice = $this->createMock(ProviderPrice::class);
        $income = new Income(
            $providerPrice,
            $incomeStatus,
            $zapCard,
            1,
            10,
            2,
            13
        );

        $this->assertEquals(1, $income->getQuantity());
        $this->assertEquals(10, $income->getPriceZak());
        $this->assertEquals(2, $income->getPriceDost());
        $this->assertEquals(13, $income->getPrice());
        $this->assertTrue($income->getZapCard()->getNumber()->isEqual($zapCard->getNumber()));

    }
}