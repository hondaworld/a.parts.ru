<?php

namespace App\Tests\Model\Card\ZapCard\Dop;

use App\Model\Card\Entity\Measure\EdIzm;
use App\Model\Contact\Entity\Country\Country;
use App\Model\Shop\Entity\ShopType\ShopType;
use App\Tests\Builder\Card\ZapCardBuilder;
use PHPUnit\Framework\TestCase;

class ZapCardDopTest extends TestCase
{
    public function testDop(): void
    {
        $zapCard = (new ZapCardBuilder())->build();

        $country = new Country('Страна', '123');
        $shopType = new ShopType('Тип магазина');
        $edIzm = new EdIzm('Ед. изм', 'ед', '1');

        $zapCard->updateDop($country, $shopType, $edIzm);
        $this->assertEquals($country, $zapCard->getCountry());
        $this->assertEquals($shopType, $zapCard->getShopType());
        $this->assertEquals($edIzm, $zapCard->getEdIzm());
        $zapCard->updateDop(null, $shopType, $edIzm);
        $this->assertNull($zapCard->getCountry());
        $this->assertEquals($shopType, $zapCard->getShopType());
        $this->assertEquals($edIzm, $zapCard->getEdIzm());
    }
}