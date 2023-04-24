<?php

namespace App\Tests\Model\Income\Income\ChangeReturnQuantity;

use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ChangeReturnQuantityTest extends TestCase
{
    public function testQuantity(): void
    {
        $income = (new IncomeBuilder(10, 10, 10, 4, 0))->build();
        $income->returnQuantity(6);
        $this->assertEquals(6, $income->getQuantityReturn());
        $this->assertEquals(4, $income->getQuantityIn());
    }

    public function testQuantityMore(): void
    {
        $income = (new IncomeBuilder(10, 10, 10, 5, 0))->build();
        $this->expectExceptionMessage('Списываемое количество больше доступного');
        $income->returnQuantity(6);
    }

    public function testQuantitySklad(): void
    {
        $income = (new IncomeBuilder(10, 10, 10, 4, 0))->withIncomeSklad()->build();
        $income->getFirstSklad()->returnQuantity(6);
        $this->assertEquals(6, $income->getFirstSklad()->getQuantityReturn());
        $this->assertEquals(4, $income->getFirstSklad()->getQuantityIn());
    }

    public function testQuantitySkladMore(): void
    {
        $income = (new IncomeBuilder(10, 10, 10, 5, 0))->withIncomeSklad()->build();
        $this->expectExceptionMessage('Списываемое количество больше доступного');
        $income->getFirstSklad()->returnQuantity(6);
    }
}