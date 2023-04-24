<?php

namespace App\Tests\Model\Order\Good;

use App\Model\Card\Entity\Stock\ZapCardStock;
use App\Model\Order\Entity\Good\OrderGood;
use App\Tests\Builder\Card\ZapCardBuilder;
use App\Tests\Builder\Card\ZapSkladBuilder;
use App\Tests\Builder\Manager\ManagerBuilder;
use App\Tests\Builder\Order\OrderBuilder;
use App\Tests\Builder\Provider\ProviderPriceBuilder;
use PHPUnit\Framework\TestCase;

class OrderGoodCreaterTest extends TestCase
{
    public function testCreateWithZapSklad(): void
    {
        $manager = (new ManagerBuilder())->build();
        $order = (new OrderBuilder())->build();
        $zapSklad = (new ZapSkladBuilder())->build();
        $zapCard = (new ZapCardBuilder())->build();
        $price = 12.6;
        $discount = 5;
        $quantity = 2;
        $no_discount = false;
        $stock = null;

        $orderGood = new OrderGood(
            $order,
            $zapCard->getNumber(),
            $zapCard->getCreater(),
            $zapSklad,
            null,
            $manager,
            $price,
            $discount,
            $quantity,
            0,
            $stock,
            $no_discount
        );

        $order->assignOrderGood($orderGood);

        $this->assertEquals($order, $orderGood->getOrder());
        $this->assertEquals($quantity, $orderGood->getQuantity());
        $this->assertEquals($zapCard->getNumber(), $orderGood->getNumber());
        $this->assertEquals($zapCard->getCreater(), $orderGood->getCreater());
        $this->assertEquals($zapSklad, $orderGood->getZapSklad());
        $this->assertEquals($price, $orderGood->getPrice());
        $this->assertEquals($discount, $orderGood->getDiscount());
        $this->assertNull($orderGood->getProviderPrice());
        $this->assertNull($orderGood->getStock());
    }

    public function testCreateWithZapSkladAndStock(): void
    {
        $manager = (new ManagerBuilder())->build();
        $order = (new OrderBuilder())->build();
        $zapSklad = (new ZapSkladBuilder())->build();
        $zapCard = (new ZapCardBuilder())->build();
        $price = 12.6;
        $discount = 0;
        $quantity = 2;
        $no_discount = false;
        $stock = $this->createMock(ZapCardStock::class);

        $orderGood = new OrderGood(
            $order,
            $zapCard->getNumber(),
            $zapCard->getCreater(),
            $zapSklad,
            null,
            $manager,
            $price,
            $discount,
            $quantity,
            0,
            $stock,
            $no_discount
        );

        $order->assignOrderGood($orderGood);

        $this->assertEquals($order, $orderGood->getOrder());
        $this->assertEquals($quantity, $orderGood->getQuantity());
        $this->assertEquals($zapCard->getNumber(), $orderGood->getNumber());
        $this->assertEquals($zapCard->getCreater(), $orderGood->getCreater());
        $this->assertEquals($zapSklad, $orderGood->getZapSklad());
        $this->assertEquals($price, $orderGood->getPrice());
        $this->assertEquals($discount, $orderGood->getDiscount());
        $this->assertNull($orderGood->getProviderPrice());
        $this->assertEquals($stock, $orderGood->getStock());
    }

    public function testCreateWithProviderPrice(): void
    {
        $manager = (new ManagerBuilder())->build();
        $order = (new OrderBuilder())->build();
        $providerPrice = (new ProviderPriceBuilder())->build();
        $zapCard = (new ZapCardBuilder())->build();
        $price = 12.6;
        $discount = 5;
        $quantity = 2;
        $no_discount = false;
        $stock = null;

        $orderGood = new OrderGood(
            $order,
            $zapCard->getNumber(),
            $zapCard->getCreater(),
            null,
            $providerPrice,
            $manager,
            $price,
            $discount,
            $quantity,
            0,
            $stock,
            $no_discount
        );

        $order->assignOrderGood($orderGood);

        $this->assertEquals($order, $orderGood->getOrder());
        $this->assertEquals($quantity, $orderGood->getQuantity());
        $this->assertEquals($zapCard->getNumber(), $orderGood->getNumber());
        $this->assertEquals($zapCard->getCreater(), $orderGood->getCreater());
        $this->assertNull($orderGood->getZapSklad());
        $this->assertEquals($price, $orderGood->getPrice());
        $this->assertEquals($discount, $orderGood->getDiscount());
        $this->assertEquals($providerPrice, $orderGood->getProviderPrice());
        $this->assertNull($orderGood->getStock());
    }

    public function testCreateWithProviderPriceAndStock(): void
    {
        $manager = (new ManagerBuilder())->build();
        $order = (new OrderBuilder())->build();
        $providerPrice = (new ProviderPriceBuilder())->build();
        $zapCard = (new ZapCardBuilder())->build();
        $price = 12.6;
        $discount = 0;
        $quantity = 2;
        $no_discount = false;
        $stock = $this->createMock(ZapCardStock::class);

        $orderGood = new OrderGood(
            $order,
            $zapCard->getNumber(),
            $zapCard->getCreater(),
            null,
            $providerPrice,
            $manager,
            $price,
            $discount,
            $quantity,
            0,
            $stock,
            $no_discount
        );

        $order->assignOrderGood($orderGood);

        $this->assertEquals($order, $orderGood->getOrder());
        $this->assertEquals($quantity, $orderGood->getQuantity());
        $this->assertEquals($zapCard->getNumber(), $orderGood->getNumber());
        $this->assertEquals($zapCard->getCreater(), $orderGood->getCreater());
        $this->assertNull($orderGood->getZapSklad());
        $this->assertEquals($price, $orderGood->getPrice());
        $this->assertEquals($discount, $orderGood->getDiscount());
        $this->assertEquals($providerPrice, $orderGood->getProviderPrice());
        $this->assertEquals($stock, $orderGood->getStock());
    }
}