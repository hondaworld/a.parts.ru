<?php

namespace App\Tests\Model\Order\Good;

use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use App\Tests\Builder\Card\ZapSkladBuilder;
use App\Tests\Builder\Income\IncomeBuilder;
use App\Tests\Builder\Manager\ManagerBuilder;
use App\Tests\Builder\Order\OrderBuilder;
use App\Tests\Builder\Order\OrderGoodBuilder;
use PHPUnit\Framework\TestCase;

class AddExpenseSkladTest extends TestCase
{
    public function testExpenseSklad(): void
    {
        $manager = (new ManagerBuilder())->build();
        $order = (new OrderBuilder())->build();
        $creater = $this->createMock(Creater::class);
        $number = '15400plma03';
        $zapSklad = (new ZapSkladBuilder())->build();

        $good = (new OrderGoodBuilder($order, $number, $creater, 10, 0, 4))->withZapSklad($zapSklad)->build();
        $order->assignOrderGood($good);

        $income = (new IncomeBuilder(4, 4, 0, 1, 0))
            ->withIncomeSklad($zapSklad)
            ->build();

        $income1 = (new IncomeBuilder(6, 5, 0, 2, 0))
            ->withIncomeSklad($zapSklad)
            ->build();

        $incomes = [$income, $income1];

        $this->assertCount(0, $good->getZapCardReserve());

        $zapSkladTo = (new ZapSkladBuilder(1))->build();
        $good->reserve($incomes, $manager, false, true, $zapSkladTo);

        $this->assertCount(2, $good->getZapCardReserve());
        $this->assertCount(0, $good->getExpenses());
        $this->assertCount(2, $good->getExpenseSklads());

        $zapCardReserve = $good->getZapCardReserve()[0];

        $this->assertEquals(3, $zapCardReserve->getQuantity());
        $this->assertEquals($income, $zapCardReserve->getIncome());
        $this->assertEquals($zapSklad, $zapCardReserve->getZapSklad());
        $this->assertNull($zapCardReserve->getDateofclosed());

        $this->assertEquals(4, $income->getReserve());
        $this->assertEquals(4, $income->getSkladByZapSklad($zapSklad)->getReserve());

        $zapCardReserve = $good->getZapCardReserve()[1];

        $this->assertEquals(1, $zapCardReserve->getQuantity());
        $this->assertEquals($income1, $zapCardReserve->getIncome());
        $this->assertEquals($zapSklad, $zapCardReserve->getZapSklad());
        $this->assertNull($zapCardReserve->getDateofclosed());

        $this->assertEquals(3, $income1->getReserve());
        $this->assertEquals(3, $income1->getSkladByZapSklad($zapSklad)->getReserve());

        $expense = $good->getExpenseSklads()[0];
        $this->assertEquals(3, $expense->getQuantity());
        $this->assertEquals($income, $expense->getIncome());
        $this->assertEquals($zapSklad, $expense->getZapSklad());
        $this->assertEquals($zapSkladTo, $expense->getZapSkladTo());
        $this->assertEquals($income->getZapCard(), $expense->getZapCard());

        $expense = $good->getExpenseSklads()[1];
        $this->assertEquals(1, $expense->getQuantity());
        $this->assertEquals($income1, $expense->getIncome());
        $this->assertEquals($zapSklad, $expense->getZapSklad());
        $this->assertEquals($zapSkladTo, $expense->getZapSkladTo());
        $this->assertEquals($income->getZapCard(), $expense->getZapCard());
    }

    public function testExpenseSkladStatus(): void
    {
        $manager = (new ManagerBuilder())->build();
        $order = (new OrderBuilder())->build();
        $creater = $this->createMock(Creater::class);
        $number = '15400plma03';
        $zapSklad = (new ZapSkladBuilder())->build();

        $good = (new OrderGoodBuilder($order, $number, $creater, 10, 0, 4))->withZapSklad($zapSklad)->build();
        $order->assignOrderGood($good);

        $income = (new IncomeBuilder(4, 4, 0, 1, 0))
            ->withIncomeSklad($zapSklad)
            ->build();

        $income1 = (new IncomeBuilder(6, 5, 0, 2, 0))
            ->withIncomeSklad($zapSklad)
            ->build();

        $incomes = [$income, $income1];

        $this->assertCount(0, $good->getZapCardReserve());

        $zapSkladTo = (new ZapSkladBuilder(1))->build();
        $good->reserve($incomes, $manager, false, true, $zapSkladTo);

        $expenseSklad = $good->getExpenseSklads()[0];

        $this->assertEquals(ExpenseSklad::ADDED, $expenseSklad->getStatus());
        $this->assertEquals(0, $expenseSklad->getQuantityPicking());
        $this->assertNull($expenseSklad->getManager());

        $expenseSklad->increaseQuantityPicking(2);
        $this->assertEquals(2, $expenseSklad->getQuantityPicking());
        $expenseSklad->increaseQuantityPicking(1);
        $this->assertEquals(3, $expenseSklad->getQuantityPicking());

        $expenseSklad->unPicking();
        $this->assertEquals(0, $expenseSklad->getQuantityPicking());

        $expenseSklad->increaseQuantityPicking(3);
        $expenseSklad->pack($manager);
        $this->assertEquals(ExpenseSklad::PACKED, $expenseSklad->getStatus());
        $this->assertEquals(3, $expenseSklad->getQuantityPicking());
        $this->assertEquals($manager, $expenseSklad->getManager());

        $expenseSklad->unPack();
        $this->assertEquals(ExpenseSklad::ADDED, $expenseSklad->getStatus());
        $this->assertEquals(0, $expenseSklad->getQuantityPicking());
        $this->assertNull($expenseSklad->getManager());

        $expenseSklad->send();
        $this->assertEquals(ExpenseSklad::SENT, $expenseSklad->getStatus());

        $this->assertEquals(0, $expenseSklad->getQuantityIncome());
        $expenseSklad->increaseQuantityIncome(2);
        $this->assertEquals(2, $expenseSklad->getQuantityIncome());
        $expenseSklad->increaseQuantityIncome(1);
        $this->assertEquals(3, $expenseSklad->getQuantityIncome());

        $expenseSklad->unIncomeFromSklad();
        $this->assertEquals(0, $expenseSklad->getQuantityIncome());

        $expenseSklad->incomeOnSklad();
        $this->assertEquals(ExpenseSklad::INCOME, $expenseSklad->getStatus());
    }
}