<?php

namespace App\Tests\Model\Card\ZapCard\ZapSkladLocation;

use App\Model\Card\Entity\Location\ZapSkladLocation;
use App\Model\Shop\Entity\Location\ShopLocation;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\Card\ZapCardBuilder;
use App\Tests\Builder\Card\ZapSkladBuilder;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ZapSkladLocationCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapSklad = (new ZapSkladBuilder())->build();
        $shopLocation = new ShopLocation('Название', 'Название короткое');
        $zapSkladLocation = new ZapSkladLocation($zapCard, $zapSklad, $shopLocation, 10, true, 15);

        $this->assertEquals($shopLocation, $zapSkladLocation->getLocation());
        $this->assertEquals(10, $zapSkladLocation->getQuantityMin());
        $this->assertEquals(15, $zapSkladLocation->getQuantityMax());
        $this->assertTrue($zapSkladLocation->getQuantityMinIsReal());
    }

    public function testCreateDefault(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapSklad = (new ZapSkladBuilder())->build();
        $zapSkladLocation = new ZapSkladLocation($zapCard, $zapSklad);

        $this->assertNull($zapSkladLocation->getLocation());
        $this->assertEquals(0, $zapSkladLocation->getQuantityMin());
        $this->assertEquals(0, $zapSkladLocation->getQuantityMax());
        $this->assertFalse($zapSkladLocation->getQuantityMinIsReal());
    }

    public function testAssignToZapCard(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapSklad = $this->createMock(ZapSklad::class);
        $zapSklad->method('getId')->willReturn(ZapSklad::OSN_SKLAD_ID);
        $zapCard->assignLocation($zapSklad);

        $this->assertCount(1, $zapCard->getLocations());
    }

    public function testSomeAssignToZapCard(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapSklad = $this->createMock(ZapSklad::class);
        $zapSklad->method('getId')->willReturn(ZapSklad::MSK);
        $zapCard->assignLocation($zapSklad);

        $zapSklad = $this->createMock(ZapSklad::class);
        $zapSklad->method('getId')->willReturn(ZapSklad::MSK);

        $zapCard->assignLocation($zapSklad);
        $this->assertCount(1, $zapCard->getLocations());
    }

    public function testSomeDifferentAssignToZapCard(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapSklad = $this->createMock(ZapSklad::class);
        $zapSklad->method('getId')->willReturn(ZapSklad::MSK);
        $zapCard->assignLocation($zapSklad);

        $zapSklad = $this->createMock(ZapSklad::class);
        $zapSklad->method('getId')->willReturn(ZapSklad::SPB);

        $zapCard->assignLocation($zapSklad);
        $this->assertCount(2, $zapCard->getLocations());
    }
}