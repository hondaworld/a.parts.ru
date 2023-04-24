<?php

namespace App\Tests\Model\Card\ZapCard\Price;

use App\Tests\Builder\Card\ZapCardBuilder;
use App\Tests\Builder\Provider\ProviderPriceBuilder;
use PHPUnit\Framework\TestCase;

class ZapCardPriceTest extends TestCase
{
    public function testPrice(): void
    {
        $zapCard = (new ZapCardBuilder())->build();

        $providerPrice = (new ProviderPriceBuilder())->build();

        $zapCard->updatePrice('34.67', '12.45', $providerPrice, $providerPrice->getCurrency());
        $this->assertEquals(34.67, $zapCard->getPrice());
        $this->assertEquals(12.45, $zapCard->getCurrencyPrice());
        $this->assertEquals($providerPrice, $zapCard->getCurrencyProviderPrice());
        $this->assertEquals($providerPrice->getCurrency(), $zapCard->getCurrency());

        $zapCard->updatePrice('21.55', '3.35', null, null);
        $this->assertEquals(21.55, $zapCard->getPrice());
        $this->assertEquals(3.35, $zapCard->getCurrencyPrice());
        $this->assertNull($zapCard->getCurrencyProviderPrice());
        $this->assertNull($zapCard->getCurrency());

        $zapCard->updatePrice(null, null, null, null);
        $this->assertEquals(0, $zapCard->getPrice());
        $this->assertEquals(0, $zapCard->getCurrencyPrice());
        $this->assertNull($zapCard->getCurrencyProviderPrice());
        $this->assertNull($zapCard->getCurrency());
    }
}