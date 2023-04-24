<?php

namespace App\Tests\Model\Detail\PriceExclude;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Detail\Entity\PriceExclude\DetailProviderPriceExclude;
use App\Tests\Builder\Provider\ProviderPriceBuilder;
use PHPUnit\Framework\TestCase;

class PriceExcludeCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $creater = new Creater('Honda', 'Хонда', true, 'shopPrice1', null, null);
        $number = new DetailNumber('15400PLMA03');
        $providerPrice = (new ProviderPriceBuilder())->build();

        $priceExclude = new DetailProviderPriceExclude($number, $creater, $providerPrice);

        $this->assertTrue($priceExclude->getNumber()->isEqual($number));
        $this->assertEquals($creater, $priceExclude->getCreater());
        $this->assertEquals($providerPrice, $priceExclude->getProviderPrice());
    }
}