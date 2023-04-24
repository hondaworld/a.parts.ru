<?php

namespace App\Tests\Model\Order\Good;

use App\Model\Detail\Entity\Creater\Creater;
use App\Tests\Builder\Card\ZapSkladBuilder;
use App\Tests\Builder\Income\IncomeBuilder;
use App\Tests\Builder\Order\OrderBuilder;
use App\Tests\Builder\Order\OrderGoodBuilder;
use App\Tests\Builder\Provider\ProviderPriceBuilder;
use PHPUnit\Framework\TestCase;

class OrderGoodUpdateTest extends TestCase
{
    public function testUpdateQuantity(): void
    {
        $order = (new OrderBuilder())->build();
        $creater = $this->createMock(Creater::class);
        $number = '15400plma03';
        $zapSklad = (new ZapSkladBuilder())->build();

        $good = (new OrderGoodBuilder($order, $number, $creater, 10, 0, 4))->withZapSklad($zapSklad)->build();
        $order->assignOrderGood($good);

        $good->updateQuantity(6);
        $this->assertEquals(6, $good->getQuantity());
    }

    public function testSplitQuantity(): void
    {
        $order = (new OrderBuilder())->build();
        $creater = $this->createMock(Creater::class);
        $number = '15400plma03';
        $zapSklad = (new ZapSkladBuilder())->build();

        $good = (new OrderGoodBuilder($order, $number, $creater, 10, 0, 4))->withZapSklad($zapSklad)->build();
        $order->assignOrderGood($good);

        $orderGoodNew = $good->splitQuantity(3, 1);
        $this->assertEquals(3, $good->getQuantity());

        $good1 = $order->getOrderGoods()[1];
        $this->assertEquals(1, $good1->getQuantity());
        $this->assertEquals(1, $orderGoodNew->getQuantity());
    }

    public function testUpdateProviderPrice(): void
    {
        $order = (new OrderBuilder())->build();
        $creater = $this->createMock(Creater::class);
        $number = '15400plma03';
        $zapSklad = (new ZapSkladBuilder())->build();

        $good = (new OrderGoodBuilder($order, $number, $creater, 10, 0, 4))->withZapSklad($zapSklad)->build();
        $order->assignOrderGood($good);

        $this->assertEquals($zapSklad, $good->getZapSklad());
        $this->assertNull($good->getProviderPrice());

        $providerPrice = (new ProviderPriceBuilder())->build();
        $good->updateProviderPrice($providerPrice);

        $this->assertEquals($providerPrice, $good->getProviderPrice());
        $this->assertNull($good->getZapSklad());
        $this->assertNull($good->getIncome());

        $income = (new IncomeBuilder())->build();
        $good->updateIncome($income);
        $this->assertEquals($income, $good->getIncome());
    }

    public function testUpdateZapSklad(): void
    {
        $order = (new OrderBuilder())->build();
        $creater = $this->createMock(Creater::class);
        $number = '15400plma03';
        $zapSklad = (new ZapSkladBuilder())->build();
        $providerPrice = (new ProviderPriceBuilder())->build();
        $income = (new IncomeBuilder())->build();

        $good = (new OrderGoodBuilder($order, $number, $creater, 10, 0, 4))->withProviderPrice($providerPrice)->build();
        $order->assignOrderGood($good);
        $good->updateIncome($income);

        $this->assertEquals($providerPrice, $good->getProviderPrice());
        $this->assertNull($good->getZapSklad());

        $good->updateZapSklad($zapSklad);
        $this->assertEquals($zapSklad, $good->getZapSklad());
        $this->assertNull($good->getProviderPrice());
        $this->assertNull($good->getIncome());
    }

    public function testUpdatePrice(): void
    {
        $order = (new OrderBuilder())->build();
        $creater = $this->createMock(Creater::class);
        $number = '15400plma03';
        $zapSklad = (new ZapSkladBuilder())->build();

        $good = (new OrderGoodBuilder($order, $number, $creater, 10, 0, 4))->withZapSklad($zapSklad)->build();
        $order->assignOrderGood($good);

        $good->updatePrice(30.56, 12);

        $this->assertEquals(30.56, $good->getPrice());
        $this->assertEquals(12, $good->getDiscount());
    }
}