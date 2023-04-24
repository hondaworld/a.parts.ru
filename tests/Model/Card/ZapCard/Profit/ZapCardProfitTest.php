<?php

namespace App\Tests\Model\Card\ZapCard\Profit;

use App\Tests\Builder\Card\ZapCardBuilder;
use PHPUnit\Framework\TestCase;

class ZapCardProfitTest extends TestCase
{
    public function testProfit(): void
    {
        $zapCard = (new ZapCardBuilder())->build();

        $zapCard->updateProfit('34.67', 12);
        $this->assertEquals(34.67, $zapCard->getPrice1());
        $this->assertEquals(12, $zapCard->getProfit());

        $zapCard->updateProfit(null, null);
        $this->assertEquals(0, $zapCard->getPrice1());
        $this->assertEquals(0, $zapCard->getProfit());
    }
}