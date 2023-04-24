<?php

namespace App\Tests\Model\Shop\ShopLocation;

use App\Model\Shop\Entity\Location\ShopLocation;
use PHPUnit\Framework\TestCase;

class ShopLocationTest extends TestCase
{
    public function testCreate(): void
    {
        $shopLocation = new ShopLocation('Название', 'Кратко');
        $this->assertEquals('Название', $shopLocation->getName());
        $this->assertEquals('Кратко', $shopLocation->getNameShort());
    }

    public function testUdate(): void
    {
        $shopLocation = new ShopLocation('Название', 'Кратко');
        $shopLocation->update('Название новое', 'Кратко новое');
        $this->assertEquals('Название новое', $shopLocation->getName());
        $this->assertEquals('Кратко новое', $shopLocation->getNameShort());
    }
}