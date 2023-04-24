<?php

namespace App\Tests\Model\Firm\Schet;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use App\Tests\Builder\Firm\SchetBuilder;
use App\Tests\Builder\Order\OrderBuilder;
use App\Tests\Builder\Order\OrderGoodBuilder;
use PHPUnit\Framework\TestCase;

class SchetAttachGoodsTest extends TestCase
{
    public function testAttachFromOrderGoods(): void
    {
        $order = (new OrderBuilder())->build();
        $creater = new Creater('Honda', 'Хонда', true, '', null, null);
        $orderGood = (new OrderGoodBuilder($order, '15400PLMA03', $creater, 100, 20, 2))->build();
        $schet = (new SchetBuilder())->build();

        $orderGood->updateSchet($schet);
        $schet->assignOrderGood($orderGood);

        $schet->attachGoodsFromOrderGoods();

        $this->assertCount(1, $schet->getSchetGoods());

        $schetGood = $schet->getSchetGoods()[0];

        $this->assertEquals($orderGood->getNumber(), $schetGood->getNumber());
        $this->assertEquals($orderGood->getCreater(), $schetGood->getCreater());
        $this->assertEquals($orderGood->getDiscountPrice(), $schetGood->getPrice());
        $this->assertEquals($orderGood->getQuantity(), $schetGood->getQuantity());
        $this->assertEquals($orderGood, $schetGood->getOrderGood());
    }

    public function testAttachFromOrderGood(): void
    {
        $order = (new OrderBuilder())->build();
        $creater = new Creater('Honda', 'Хонда', true, '', null, null);
        $orderGood = (new OrderGoodBuilder($order, '15400PLMA03', $creater, 100, 20, 2))->build();
        $schet = (new SchetBuilder())->build();

        $orderGood->updateSchet($schet);
        $schet->assignOrderGood($orderGood);

        $schet->attachGood($orderGood, new DetailNumber('15400PLMA04'), $creater, 4, 150);

        $this->assertCount(1, $schet->getSchetGoods());

        $schetGood = $schet->getSchetGoods()[0];

        $this->assertEquals(new DetailNumber('15400PLMA04'), $schetGood->getNumber());
        $this->assertEquals($creater, $schetGood->getCreater());
        $this->assertEquals(150, $schetGood->getPrice());
        $this->assertEquals(4, $schetGood->getQuantity());
        $this->assertEquals($orderGood, $schetGood->getOrderGood());
    }
}