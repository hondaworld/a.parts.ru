<?php

namespace App\Tests\Model\Firm\Schet;

use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Order\Entity\AlertType\OrderAlertType;
use App\Tests\Builder\Firm\SchetBuilder;
use App\Tests\Builder\Order\OrderBuilder;
use App\Tests\Builder\Order\OrderGoodBuilder;
use PHPUnit\Framework\TestCase;

class SchetPayTest extends TestCase
{
    public function testPay(): void
    {
        $orderAlertType = $this->createMock(OrderAlertType::class);
        $order = (new OrderBuilder())->build();
        $creater = new Creater('Honda', 'Хонда', true, '', null, null);
        $orderGood = (new OrderGoodBuilder($order, '15400PLMA03', $creater, 100, 20, 2))->build();
        $schet = (new SchetBuilder())->build();

        $orderGood->updateSchet($schet);
        $schet->assignOrderGood($orderGood);

        $schet->attachGoodsFromOrderGoods();

        $this->assertCount(1, $schet->getSchetGoods());

        $schetGood = $schet->getSchetGoods()[0];

        $d = new \DateTime();
        $schet->pay($d, 223.44, $orderAlertType);

        $this->assertEquals($d, $schet->getDateofpaid());
        $this->assertTrue($schet->isPaid());
        $this->assertEquals(223.44, $schet->getSumm());

        $this->assertCount(1, $schetGood->getOrderGood()->getAlerts());
    }
}