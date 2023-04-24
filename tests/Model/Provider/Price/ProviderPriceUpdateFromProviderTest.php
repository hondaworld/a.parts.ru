<?php

namespace App\Tests\Model\Provider\Price;

use App\Model\Finance\Entity\Currency\Currency;
use App\Tests\Builder\Provider\ProviderBuilder;
use App\Tests\Builder\Provider\ProviderPriceBuilder;
use PHPUnit\Framework\TestCase;

class ProviderPriceUpdateFromProviderTest extends TestCase
{
    public function testUpdate(): void
    {
        $provider = (new ProviderBuilder())->build();
        $price1 = (new ProviderPriceBuilder('Прайс 1'))->build();
        $price2 = (new ProviderPriceBuilder(' Прайс 2'))->build();

        $provider->assignPrice($price1);
        $provider->assignPrice($price2);

        $currency = new Currency(
            2,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            true,
            ''
        );
        $provider->updateCurrencyForAllProviderPrices($currency, 2, 5);

        $this->assertCount(2, $provider->getPrices());

        foreach ($provider->getPrices() as $price) {
            $this->assertEquals($currency, $price->getCurrency());
            $this->assertEquals(2, $price->getKoef());
            $this->assertEquals(5, $price->getCurrencyOwn());
        }
    }
}