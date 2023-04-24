<?php

namespace App\Tests\Model\Card\ZapCard\PriceService;

use App\Tests\Builder\Card\ZapCardBuilder;
use App\Tests\Builder\Provider\ProviderPriceBuilder;
use PHPUnit\Framework\TestCase;

class ZapCardPriceServiceTest extends TestCase
{
    public function testPriceService(): void
    {
        $zapCard = (new ZapCardBuilder())->build();

        $providerPrice = (new ProviderPriceBuilder())->build();

        $zapCard->updatePriceService('34.67');
        $this->assertEquals(34.67, $zapCard->getPriceService());

        $zapCard->updatePriceService(null);
        $this->assertEquals(0, $zapCard->getPriceService());
    }
}