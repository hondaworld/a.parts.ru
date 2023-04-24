<?php

namespace App\Tests\Model\Income\Income\ShipBetweenSklads;

use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\Income\IncomeBuilder;
use App\Tests\Builder\Manager\ManagerBuilder;
use PHPUnit\Framework\TestCase;

class ShipTest extends TestCase
{
    public function testShip(): void
    {
        $manager = (new ManagerBuilder())->build();
        $zapSklad = $this->createMock(ZapSklad::class);
        $zapSklad->method('getId')->willReturn(1);
        $zapSklad_to = $this->createMock(ZapSklad::class);
        $zapSklad_to->method('getId')->willReturn(2);
        $income = (new IncomeBuilder(5, 5, 0, 1, 0))->withIncomeSklad($zapSklad)->build();

        $sklad = $income->getSkladByZapSklad($zapSklad);

        $this->assertEquals(1, $sklad->getReserve());

        $income->addReserveByZapSklad($income->getSkladByZapSklad($zapSklad), $zapSklad_to, 3, $manager);
        $this->assertEquals(4, $sklad->getReserve());

        $income->shipBetweenSklads($zapSklad, $zapSklad_to, 3);
        $skladTo = $income->getSkladByZapSklad($zapSklad_to);

        $this->assertEquals(5, $income->getQuantity());
        $this->assertEquals(2, $income->getQuantityIn());
        $this->assertEquals(3, $income->getQuantityPath());
        $this->assertEquals(4, $income->getReserve());

        $this->assertEquals(1, $sklad->getReserve());
        $this->assertEquals(2, $sklad->getQuantity());
        $this->assertEquals(2, $sklad->getQuantityIn());
        $this->assertEquals(0, $sklad->getQuantityPath());

        $this->assertEquals(3, $skladTo->getReserve());
        $this->assertEquals(3, $skladTo->getQuantity());
        $this->assertEquals(0, $skladTo->getQuantityIn());
        $this->assertEquals(3, $skladTo->getQuantityPath());
    }
}