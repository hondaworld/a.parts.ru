<?php

namespace App\Tests\Model\Card\ZapCardStock;

use App\ReadModel\Card\ZapCardStockView;
use PHPUnit\Framework\TestCase;

class ZapCardStockViewTest extends TestCase
{
    public function testCheck(): void
    {
        $zapCardStockView = new ZapCardStockView();
        $this->assertFalse($zapCardStockView->isStock());

        $zapCardStockView->stockID = 1;
        $this->assertTrue($zapCardStockView->isStock());

        $this->assertEquals(0, $zapCardStockView->getPrice());
        $this->assertFalse($zapCardStockView->hasPrice());
    }

    public function testPrice(): void
    {
        $zapCardStockView = new ZapCardStockView();
        $zapCardStockView->stockID = 1;
        $zapCardStockView->price_stock = 20.2;

        $this->assertEquals(20.2, $zapCardStockView->getPrice());
        $this->assertTrue($zapCardStockView->hasPrice());
    }
}