<?php

namespace App\Tests\Model\Order\Good;

use App\Model\Detail\Entity\Creater\Creater;
use App\Tests\Builder\Card\ZapSkladBuilder;
use App\Tests\Builder\Income\IncomeBuilder;
use App\Tests\Builder\Manager\ManagerBuilder;
use App\Tests\Builder\Order\OrderBuilder;
use App\Tests\Builder\Order\OrderGoodBuilder;
use PHPUnit\Framework\TestCase;

class DeleteReserveTest extends TestCase
{
    public function testDelete(): void
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

        $good->reserve($incomes, $manager);
        $good->removeReserve();

        $this->assertCount(0, $good->getZapCardReserve());

        $this->assertEquals(1, $income->getReserve());
        $this->assertEquals(1, $income->getSkladByZapSklad($zapSklad)->getReserve());

        $this->assertEquals(2, $income1->getReserve());
        $this->assertEquals(2, $income1->getSkladByZapSklad($zapSklad)->getReserve());
    }
}