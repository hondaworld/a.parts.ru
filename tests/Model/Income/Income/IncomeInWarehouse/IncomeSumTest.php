<?php

namespace App\Tests\Model\Income\Income\IncomeInWarehouse;

use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class IncomeSumTest extends TestCase
{
    public function testIncomeSum(): void
    {
        $income = (new IncomeBuilder(3))->build();
        $income->updatePrices(100, 50, 140);
        $this->assertEquals(140 * 3, $income->getSum());
    }
}