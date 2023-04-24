<?php

namespace App\Tests\Model\Shop\ShopType;

use App\Model\Shop\Entity\Reseller\Reseller;
use App\Model\Shop\Entity\ShopType\ShopType;
use PHPUnit\Framework\TestCase;

class ShopTypeTest extends TestCase
{
    public function testCreate(): void
    {
        $shopType = new ShopType('Название');
        $this->assertEquals('Название', $shopType->getName());
    }

    public function testUdate(): void
    {
        $shopType = new ShopType('Название');
        $shopType->update('Название новое');
        $this->assertEquals('Название новое', $shopType->getName());
    }
}