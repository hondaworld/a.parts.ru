<?php

namespace App\Tests\Model\Shop\DeliveryTk;

use App\Model\Shop\Entity\DeliveryTk\DeliveryTk;
use PHPUnit\Framework\TestCase;

class DeliveryTkTest extends TestCase
{
    public function testCreate(): void
    {
        $deliveryTk = new DeliveryTk('ТК доставки', 'http://ss.ru', 'asas');
        $this->assertEquals('ТК доставки', $deliveryTk->getName());
        $this->assertEquals('http://ss.ru', $deliveryTk->getHttp());
        $this->assertEquals('asas', $deliveryTk->getSmsText());
    }

    public function testUdate(): void
    {
        $deliveryTk = new DeliveryTk('ТК доставки', 'http://ss.ru', 'asas');
        $deliveryTk->update('ТК доставки другая', 'http://ssaa.ru', 'asas1');
        $this->assertEquals('ТК доставки другая', $deliveryTk->getName());
        $this->assertEquals('http://ssaa.ru', $deliveryTk->getHttp());
        $this->assertEquals('asas1', $deliveryTk->getSmsText());
    }
}