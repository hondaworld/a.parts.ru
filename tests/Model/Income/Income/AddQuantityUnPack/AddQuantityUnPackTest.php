<?php

namespace App\Tests\Model\Income\Income\AddQuantityUnPack;

use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class AddQuantityUnPackTest extends TestCase
{
    public function testQuantity(): void
    {
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->build();
        $income->addQuantityUnPack(6);
        $this->assertEquals(6, $income->getQuantityUnPack());
        $income->addQuantityUnPack(4);
        $this->assertEquals(10, $income->getQuantityUnPack());
    }

    public function testQuantityMore(): void
    {
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->build();
        $this->expectExceptionMessage('В приходе нет такого количества');
        $income->addQuantityUnPack(11);
    }
}