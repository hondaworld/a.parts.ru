<?php

namespace App\Tests\Model\Order\Good;

use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\Card\ZapSkladBuilder;
use App\Tests\Builder\Income\IncomeBuilder;
use App\Tests\Builder\Manager\ManagerBuilder;
use App\Tests\Builder\Order\OrderBuilder;
use App\Tests\Builder\Order\OrderGoodBuilder;
use PHPUnit\Framework\TestCase;

class SendExpenseSkladTest extends TestCase
{
    public function testSend(): void
    {
        $manager = (new ManagerBuilder())->build();
        $order = (new OrderBuilder())->build();
        $creater = $this->createMock(Creater::class);
        $number = '15400plma03';

        $zapSklad = $this->createMock(ZapSklad::class);
        $zapSklad->method('getId')->willReturn(1);
        $zapSkladTo = $this->createMock(ZapSklad::class);
        $zapSkladTo->method('getId')->willReturn(2);

        $good = (new OrderGoodBuilder($order, $number, $creater, 10, 0, 3))->withZapSklad($zapSklad)->build();
        $order->assignOrderGood($good);

        $income = (new IncomeBuilder(5, 5, 0, 1, 0))
            ->withIncomeSklad($zapSklad)
            ->build();

        $sklad = $income->getSkladByZapSklad($zapSklad);
        $incomes = [$income];

        $good->reserve($incomes, $manager, false, true, $zapSkladTo);

        $zapCardReserve = $good->getZapCardReserve()[0];
        $expenseSklad = $good->getExpenseSklads()[0];

        $this->assertEquals(4, $income->getReserve());
        $this->assertEquals($zapSklad->getId(), $good->getZapSklad()->getId());
        $this->assertEquals($zapSklad->getId(), $zapCardReserve->getZapSklad()->getId());

        $good->shipBetweenSklads($good->getExpenseSklads()[0]);
        $skladTo = $income->getSkladByZapSklad($zapSkladTo);

        $this->assertEquals(5, $income->getQuantity());
        $this->assertEquals(2, $income->getQuantityIn());
        $this->assertEquals(3, $income->getQuantityPath());
        $this->assertEquals(4, $income->getReserve());

        $this->assertEquals(1, $sklad->getReserve());
        $this->assertEquals(2, $sklad->getQuantity());
        $this->assertEquals(2, $sklad->getQuantityIn());
        $this->assertEquals(0, $sklad->getQuantityPath());

        $this->assertEquals(3, $skladTo->getReserve());
        $this->assertEquals(3, $skladTo->getQuantity());
        $this->assertEquals(0, $skladTo->getQuantityIn());
        $this->assertEquals(3, $skladTo->getQuantityPath());

        $this->assertEquals($zapSkladTo->getId(), $good->getZapSklad()->getId());
        $this->assertEquals($zapSkladTo->getId(), $zapCardReserve->getZapSklad()->getId());
        $this->assertNull($zapCardReserve->getDateofclosed());
    }
}