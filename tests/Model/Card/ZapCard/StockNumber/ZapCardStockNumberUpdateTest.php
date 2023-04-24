<?php

namespace App\Tests\Model\Card\ZapCard\StockNumber;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Stock\ZapCardStock;
use App\Model\Card\Entity\StockNumber\ZapCardStockNumber;
use App\Model\Detail\Entity\Creater\Creater;
use PHPUnit\Framework\TestCase;

class ZapCardStockNumberUpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $zapCadStock = new ZapCardStock('Название', 'Текст');
        $zapCadStock1 = new ZapCardStock('Название 1', 'Текст 1');
        $number = new DetailNumber('15400PLMA03');
        $creater = new Creater('Honda', 'Хонда', true, 'shopTable', null, null);
        $zapCardStockNumber = new ZapCardStockNumber($zapCadStock, $number, $creater, '23.66');

        $zapCardStockNumber->update($zapCadStock1, '42.76');

        $this->assertEquals($zapCadStock1, $zapCardStockNumber->getStock());
        $this->assertEquals(42.76, $zapCardStockNumber->getPriceStock());
    }

    public function testUpdatePrice(): void
    {
        $zapCadStock = new ZapCardStock('Название', 'Текст');
        $number = new DetailNumber('15400PLMA03');
        $creater = new Creater('Honda', 'Хонда', true, 'shopTable', null, null);
        $zapCardStockNumber = new ZapCardStockNumber($zapCadStock, $number, $creater, '23.66');

        $zapCardStockNumber->updatePriceStock('42.76');

        $this->assertEquals(42.76, $zapCardStockNumber->getPriceStock());

        $zapCardStockNumber->updatePriceStock(null);

        $this->assertEquals(0, $zapCardStockNumber->getPriceStock());
    }
}