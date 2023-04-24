<?php

namespace App\Tests\Model\Shop\Delivery;

use App\Model\Shop\Entity\Delivery\Delivery;
use PHPUnit\Framework\TestCase;

class DeliveryTest extends TestCase
{
    public function testCreate(): void
    {
        $delivery = new Delivery('Доставка', '3', '1', false, '2', true, false, true, false, 'path', 1);
        $this->assertEquals('Доставка', $delivery->getName());
        $this->assertEquals('3', $delivery->getPorog());
        $this->assertEquals('1', $delivery->getX1());
        $this->assertFalse($delivery->isPercent1());
        $this->assertEquals('2', $delivery->getX2());
        $this->assertTrue($delivery->isPercent2());
        $this->assertFalse($delivery->isTK());
        $this->assertTrue($delivery->isOwnDelivery());
        $this->assertFalse($delivery->isMain());
        $this->assertEquals('path', $delivery->getPath());
        $this->assertEquals(1, $delivery->getNumber());
    }

    public function testUdate(): void
    {
        $delivery = new Delivery('Доставка', '3', '1', false, '2', true, false, true, false, 'path', 1);
        $this->assertEquals('Доставка', $delivery->getName());
        $this->assertEquals('3', $delivery->getPorog());
        $this->assertEquals('1', $delivery->getX1());
        $this->assertFalse($delivery->isPercent1());
        $this->assertEquals('2', $delivery->getX2());
        $this->assertTrue($delivery->isPercent2());
        $this->assertFalse($delivery->isTK());
        $this->assertTrue($delivery->isOwnDelivery());
        $this->assertFalse($delivery->isMain());
        $this->assertEquals('path', $delivery->getPath());
        $this->assertEquals(1, $delivery->getNumber());
    }
}