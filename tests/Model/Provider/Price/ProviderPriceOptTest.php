<?php

namespace App\Tests\Model\Provider\Price;

use App\Model\User\Entity\Opt\Opt;
use App\Tests\Builder\Provider\ProviderBuilder;
use App\Tests\Builder\Provider\ProviderPriceBuilder;
use PHPUnit\Framework\TestCase;

class ProviderPriceOptTest extends TestCase
{
    public function testAssign(): void
    {
        $provider = (new ProviderBuilder())->build();
        $price1 = (new ProviderPriceBuilder('Прайс 1'))->build();
        $price2 = (new ProviderPriceBuilder(' Прайс 2'))->build();

        $provider->assignPrice($price1);
        $provider->assignPrice($price2);

        $opt1 = new Opt('Розница', 1);
        $opt2 = new Opt('Опт', 2);

        $price1->assignProfit($opt1, '2');
        $price1->assignProfit($opt2, '5');

        $this->assertCount(2, $price1->getProfits());
        $profit1 = $price1->getProfits()[0];
        $profit2 = $price1->getProfits()[1];

        $this->assertEquals($opt1, $profit1->getOpt());
        $this->assertEquals($opt2, $profit2->getOpt());
        $this->assertEquals(2, $profit1->getProfit());
        $this->assertEquals(5, $profit2->getProfit());

        $provider->clearPriceProfits();

        $this->assertCount(0, $price1->getProfits());
    }
}