<?php

namespace App\Tests\Model\Card\ZapCard\StockNumber;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Stock\ZapCardStock;
use App\Model\Card\Entity\StockNumber\ZapCardStockNumber;
use App\Model\Detail\Entity\Creater\Creater;
use PHPUnit\Framework\TestCase;

class ZapCardStockNumberCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $zapCadStock = new ZapCardStock('Название', 'Текст');
        $number = new DetailNumber('15400PLMA03');
        $creater = new Creater('Honda', 'Хонда', true, 'shopTable', null, null);
        $zapCardStockNumber = new ZapCardStockNumber($zapCadStock, $number, $creater, '23.66');

        $this->assertEquals($zapCadStock, $zapCardStockNumber->getStock());
        $this->assertEquals($creater, $zapCardStockNumber->getCreater());
        $this->assertEquals(23.66, $zapCardStockNumber->getPriceStock());
        $this->assertTrue($zapCardStockNumber->getNumber()->isEqual($number));
    }
}