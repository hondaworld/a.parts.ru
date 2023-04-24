<?php

namespace App\Tests\Model\Income\Income\ExpenseSklad;

use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\Income\IncomeBuilder;
use App\Tests\Builder\Manager\ManagerBuilder;
use PHPUnit\Framework\TestCase;

class IncomeExpenseSkladTest extends TestCase
{
    public function testSend(): void
    {
        $manager = (new ManagerBuilder())->build();
        $zapSklad = $this->createMock(ZapSklad::class);
        $zapSklad->method('getId')->willReturn(1);
        $zapSklad_to = $this->createMock(ZapSklad::class);
        $zapSklad_to->method('getId')->willReturn(2);
        $income = (new IncomeBuilder(5, 5, 0, 1, 0))->withIncomeSklad($zapSklad)->build();

        $sklad = $income->getSkladByZapSklad($zapSklad);

        $this->assertEquals(1, $sklad->getReserve());

        $income->addReserveByZapSklad($income->getSkladByZapSklad($zapSklad), $zapSklad_to, 3, $manager);
        $expenseSklad = $income->getExpenseSklads()[0];

        $expenseSklad->shipBetweenSklads();
        $skladTo = $income->getSkladByZapSklad($zapSklad_to);
        $this->assertCount(1, $expenseSklad->getZapCardReserveSklad());
        $expenseSklad->shippedOnSklad();

        $this->assertEquals(5, $income->getQuantity());
        $this->assertEquals(5, $income->getQuantityIn());
        $this->assertEquals(0, $income->getQuantityPath());
        $this->assertEquals(1, $income->getReserve());

        $this->assertEquals(1, $sklad->getReserve());
        $this->assertEquals(2, $sklad->getQuantity());
        $this->assertEquals(2, $sklad->getQuantityIn());
        $this->assertEquals(0, $sklad->getQuantityPath());

        $this->assertEquals(0, $skladTo->getReserve());
        $this->assertEquals(3, $skladTo->getQuantity());
        $this->assertEquals(3, $skladTo->getQuantityIn());
        $this->assertEquals(0, $skladTo->getQuantityPath());

        $this->assertCount(0, $expenseSklad->getZapCardReserveSklad());
    }
}