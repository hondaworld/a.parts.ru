<?php

namespace App\Tests\Model\Sklad\PriceGroup;

use App\Model\Sklad\Entity\PriceGroup\PriceGroup;
use App\Model\Sklad\Entity\PriceList\PriceList;
use PHPUnit\Framework\TestCase;

class PriceGroupTest extends TestCase
{
    public function testCreate(): void
    {
        $priceList = new PriceList('Название прайс-листа', 1, true, true);
        $priceGroup = new PriceGroup($priceList, 'Название', false);
        $this->assertEquals($priceList, $priceGroup->getPriceList());
        $this->assertEquals('Название', $priceGroup->getName());
        $this->assertFalse($priceGroup->isMain());
    }

    public function testUdate(): void
    {
        $priceList = new PriceList('Название прайс-листа', 1, true, true);
        $priceGroup = new PriceGroup($priceList, 'Название', false);
        $priceGroup->update('Название новое', true);
        $this->assertEquals($priceList, $priceGroup->getPriceList());
        $this->assertEquals('Название новое', $priceGroup->getName());
        $this->assertTrue($priceGroup->isMain());
    }
}