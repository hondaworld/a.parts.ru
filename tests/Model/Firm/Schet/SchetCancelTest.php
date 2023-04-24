<?php

namespace App\Tests\Model\Firm\Schet;

use App\Model\Detail\Entity\Creater\Creater;
use App\Tests\Builder\Firm\SchetBuilder;
use App\Tests\Builder\Order\OrderBuilder;
use App\Tests\Builder\Order\OrderGoodBuilder;
use PHPUnit\Framework\TestCase;

class SchetCancelTest extends TestCase
{
    public function testCancel(): void
    {
        $order = (new OrderBuilder())->build();
        $creater = new Creater('Honda', 'Хонда', true, '', null, null);
        $orderGood = (new OrderGoodBuilder($order, '15400PLMA03', $creater, 100, 0, 2))->build();
        $schet = (new SchetBuilder())->build();

        $orderGood->updateSchet($schet);

        $this->assertEquals($schet, $orderGood->getSchet());

        $orderGood->removeSchet();

        $this->assertNull($orderGood->getSchet());
    }

    public function testClearOrderGoods(): void
    {
        $order = (new OrderBuilder())->build();
        $creater = new Creater('Honda', 'Хонда', true, '', null, null);
        $orderGood = (new OrderGoodBuilder($order, '15400PLMA03', $creater, 100, 0, 2))->build();
        $schet = (new SchetBuilder())->build();

        $orderGood->updateSchet($schet);
        $schet->assignOrderGood($orderGood);

        $schet->clearOrderGoods();

        $this->assertNull($orderGood->getSchet());

        $schet->cancel('Причина удаления');

        $this->assertTrue($schet->isCanceled());
        $this->assertEquals('Причина удаления', $schet->getCancelReason());
    }
}