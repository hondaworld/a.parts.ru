<?php

namespace App\Tests\Model\Shop\PayMethod;

use App\Model\Shop\Entity\DeliveryTk\DeliveryTk;
use App\Model\Shop\Entity\PayMethod\PayMethod;
use PHPUnit\Framework\TestCase;

class PayMethodTest extends TestCase
{
    public function testCreate(): void
    {
        $payMethod = new PayMethod('Название', 'Описание', false, 1);
        $this->assertEquals('Название', $payMethod->getVal());
        $this->assertEquals('Описание', $payMethod->getDescription());
        $this->assertEquals(1, $payMethod->getNumber());
        $this->assertFalse($payMethod->isMain());
    }

    public function testUdate(): void
    {
        $payMethod = new PayMethod('Название', 'Описание', false, 1);
        $payMethod->update('Название новое', 'Описание новое', true);
        $this->assertEquals('Название новое', $payMethod->getVal());
        $this->assertEquals('Описание новое', $payMethod->getDescription());
        $this->assertEquals(1, $payMethod->getNumber());
        $this->assertTrue($payMethod->isMain());
    }
}