<?php

namespace App\Tests\Model\Income\Income\ExpenseSklad;

use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\Card\ZapSkladBuilder;
use App\Tests\Builder\Income\IncomeBuilder;
use App\Tests\Builder\Manager\ManagerBuilder;
use PHPUnit\Framework\TestCase;

class AddExpenseSkladTest extends TestCase
{
    public function testAdd(): void
    {
        $manager = (new ManagerBuilder())->build();
        $zapSklad = (new ZapSkladBuilder())->build();
        $zapSklad_to = (new ZapSkladBuilder(1))->build();

        $income = (new IncomeBuilder(5, 5, 0, 1, 0))->withIncomeSklad($zapSklad)->build();

        $this->assertCount(0, $income->getExpenseSklads());

        $income->addReserveByZapSklad($income->getSkladByZapSklad($zapSklad), $zapSklad_to, 3, $manager);

        $this->assertCount(1, $income->getExpenseSklads());

        $expenseSklad = $income->getExpenseSklads()[0];

        $this->assertCount(1, $expenseSklad->getZapCardReserveSklad());
        $this->assertEquals($zapSklad, $expenseSklad->getZapSklad());
        $this->assertEquals($zapSklad_to, $expenseSklad->getZapSkladTo());
        $this->assertEquals($income->getZapCard(), $expenseSklad->getZapCard());
        $this->assertEquals(3, $expenseSklad->getQuantity());
        $this->assertNull($expenseSklad->getOrderGood());

        $zapCardReserveSklad = $expenseSklad->getZapCardReserveSklad()[0];

        $this->assertEquals(3, $zapCardReserveSklad->getQuantity());
        $this->assertEquals($zapSklad, $zapCardReserveSklad->getZapSklad());
        $this->assertEquals($zapSklad_to, $zapCardReserveSklad->getZapSkladTo());
        $this->assertEquals($income->getZapCard(), $zapCardReserveSklad->getZapCard());
        $this->assertEquals($manager, $zapCardReserveSklad->getManager());
        $this->assertEquals($income, $zapCardReserveSklad->getIncome());
    }
}