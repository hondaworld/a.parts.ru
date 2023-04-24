<?php

namespace App\Tests\Model\Income\Income\ChangeQuantity;

use App\Model\Income\Entity\Status\IncomeStatus;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ChangeQuantityNotWarehouseIncomeTest extends TestCase
{
    public function testQuantityMore(): void
    {
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->build();
        $income->changeQuantity(15);
        $this->assertEquals(15, $income->getQuantity());
        $this->assertEquals(15, $income->getQuantityPath());
        $this->assertEquals(15, $income->getReserve());
        $this->assertEquals(0, $income->getQuantityIn());
        $this->assertEquals(0, $income->getQuantityReturn());
    }

    public function testQuantityLess(): void
    {
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->build();
        $income->changeQuantity(5);
        $this->assertEquals(5, $income->getQuantity());
        $this->assertEquals(5, $income->getQuantityPath());
        $this->assertEquals(5, $income->getReserve());
        $this->assertEquals(0, $income->getQuantityIn());
        $this->assertEquals(0, $income->getQuantityReturn());
    }
}