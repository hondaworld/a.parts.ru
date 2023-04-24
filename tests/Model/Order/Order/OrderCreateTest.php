<?php

namespace App\Tests\Model\Order\Order;

use App\Model\Order\Entity\AddReason\OrderAddReason;
use App\Model\Order\Entity\Order\Order;
use App\Tests\Builder\Manager\ManagerBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class OrderCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $user = (new UserBuilder())->build();
        $manager = (new ManagerBuilder())->build();
        $reason = $this->createMock(OrderAddReason::class);
        $order = new Order($user, $manager, $reason);

        $this->assertEquals($user, $order->getUser());
        $this->assertEquals($manager, $order->getManager());
        $this->assertEquals($reason, $order->getOrderAddReason());
        $this->assertTrue($order->isWorking());
    }

    public function testCreateReasonNull(): void
    {
        $user = (new UserBuilder())->build();
        $manager = (new ManagerBuilder())->build();
        $order = new Order($user, $manager, null);

        $this->assertEquals($user, $order->getUser());
        $this->assertEquals($manager, $order->getManager());
        $this->assertNull($order->getOrderAddReason());
        $this->assertTrue($order->isWorking());
    }

    public function testCreateManagerNull(): void
    {
        $user = (new UserBuilder())->build();
        $order = new Order($user, null, null);

        $this->assertEquals($user, $order->getUser());
        $this->assertNull($order->getManager());
        $this->assertNull($order->getOrderAddReason());
        $this->assertTrue($order->isWorking());
    }
}