<?php

namespace App\Tests\Model\Sklad\PriceList;

use App\Model\Sklad\Entity\PriceList\PriceList;
use PHPUnit\Framework\TestCase;

class PriceListTest extends TestCase
{
    public function testCreate(): void
    {
        $priceList = new PriceList('Название прайс-листа', 1, true, true);
        $this->assertEquals('Название прайс-листа', $priceList->getName());
        $this->assertEquals(1, $priceList->getKoefDealer());
        $this->assertTrue($priceList->getNoDiscount());
        $this->assertTrue($priceList->isMain());
    }

    public function testUdate(): void
    {
        $priceList = new PriceList('Название прайс-листа', 1, true, true);
        $priceList->update('Название прайс-листа новое', 1.5, false, false);
        $this->assertEquals('Название прайс-листа новое', $priceList->getName());
        $this->assertEquals(1.5, $priceList->getKoefDealer());
        $this->assertFalse($priceList->getNoDiscount());
        $this->assertFalse($priceList->isMain());
    }
}