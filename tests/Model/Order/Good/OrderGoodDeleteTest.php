<?php

namespace App\Tests\Model\Order\Good;

use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Shop\Entity\DeleteReason\DeleteReason;
use App\Tests\Builder\Card\ZapSkladBuilder;
use App\Tests\Builder\Manager\ManagerBuilder;
use App\Tests\Builder\Order\OrderBuilder;
use App\Tests\Builder\Order\OrderGoodBuilder;
use PHPUnit\Framework\TestCase;

class OrderGoodDeleteTest extends TestCase
{
    public function testDelete(): void
    {
        $manager = (new ManagerBuilder())->build();
        $order = (new OrderBuilder())->build();
        $creater = $this->createMock(Creater::class);
        $number = '15400plma03';
        $zapSklad = (new ZapSkladBuilder())->build();
        $deleteReason = new DeleteReason('тест', false);

        $good = (new OrderGoodBuilder($order, $number, $creater, 10, 0, 4))->withZapSklad($zapSklad)->build();
        $order->assignOrderGood($good);

        $good->deleteGood($deleteReason, $manager);

        $this->assertNotNull($good->getDateofdeleted());
        $this->assertTrue($good->isDeleted());
        $this->assertEquals($deleteReason, $good->getDeleteReason());
        $this->assertFalse($good->getDeleteReasonEmailed());
        $this->assertEquals($manager, $good->getDeleteManager());

        $good->deleteReasonWasEmailed();
        $this->assertTrue($good->getDeleteReasonEmailed());
    }
}