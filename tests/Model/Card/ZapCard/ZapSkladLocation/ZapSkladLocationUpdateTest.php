<?php

namespace App\Tests\Model\Card\ZapCard\ZapSkladLocation;

use App\Model\Card\Entity\Location\ZapSkladLocation;
use App\Model\Shop\Entity\Location\ShopLocation;
use App\Tests\Builder\Card\ZapCardBuilder;
use App\Tests\Builder\Card\ZapSkladBuilder;
use PHPUnit\Framework\TestCase;

class ZapSkladLocationUpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapSklad = (new ZapSkladBuilder())->build();
        $shopLocation = new ShopLocation('Название', 'Название короткое');
        $shopLocation1 = new ShopLocation('Название 1', 'Название короткое');
        $zapSkladLocation = new ZapSkladLocation($zapCard, $zapSklad, $shopLocation, 10, true, 15);

        $zapSkladLocation->update($shopLocation1, 3, false, 20);

        $this->assertEquals($shopLocation1, $zapSkladLocation->getLocation());
        $this->assertEquals(3, $zapSkladLocation->getQuantityMin());
        $this->assertEquals(20, $zapSkladLocation->getQuantityMax());
        $this->assertFalse($zapSkladLocation->getQuantityMinIsReal());
    }

    public function testUpdateQuantityMin(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapSklad = (new ZapSkladBuilder())->build();
        $shopLocation = new ShopLocation('Название', 'Название короткое');
        $shopLocation1 = new ShopLocation('Название 1', 'Название короткое');
        $zapSkladLocation = new ZapSkladLocation($zapCard, $zapSklad, $shopLocation, 10, true, 15);

        $zapSkladLocation->updateQuantityMin(6);
        $this->assertEquals(6, $zapSkladLocation->getQuantityMin());

        $zapSkladLocation->updateQuantityMin();
        $this->assertEquals(0, $zapSkladLocation->getQuantityMin());

        $zapSkladLocation->updateQuantityMin(6);
        $zapSkladLocation->updateQuantityMin(null);
        $this->assertEquals(0, $zapSkladLocation->getQuantityMin());
    }

    public function testUpdateQuantityMax(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapSklad = (new ZapSkladBuilder())->build();
        $shopLocation = new ShopLocation('Название', 'Название короткое');
        $shopLocation1 = new ShopLocation('Название 1', 'Название короткое');
        $zapSkladLocation = new ZapSkladLocation($zapCard, $zapSklad, $shopLocation, 10, true, 15);

        $zapSkladLocation->updateQuantityMax(6);
        $this->assertEquals(6, $zapSkladLocation->getQuantityMax());

        $zapSkladLocation->updateQuantityMax();
        $this->assertEquals(0, $zapSkladLocation->getQuantityMax());

        $zapSkladLocation->updateQuantityMax(6);
        $zapSkladLocation->updateQuantityMax(null);
        $this->assertEquals(0, $zapSkladLocation->getQuantityMax());
    }

    public function testUpdateShopLocation(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapSklad = (new ZapSkladBuilder())->build();
        $shopLocation = new ShopLocation('Название', 'Название короткое');
        $shopLocation1 = new ShopLocation('Название 1', 'Название короткое');
        $zapSkladLocation = new ZapSkladLocation($zapCard, $zapSklad, $shopLocation, 10, true, 15);

        $zapSkladLocation->updateShopLocation($shopLocation1);
        $this->assertEquals($shopLocation1, $zapSkladLocation->getLocation());

        $zapSkladLocation->updateShopLocation();
        $this->assertNull($zapSkladLocation->getLocation());

        $zapSkladLocation->updateShopLocation($shopLocation1);
        $zapSkladLocation->updateShopLocation(null);
        $this->assertNull($zapSkladLocation->getLocation());
    }
}