<?php

namespace App\Tests\Model\Provider\Price;

use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Provider\Entity\Price\Price;
use App\Tests\Builder\Provider\ProviderPriceBuilder;
use PHPUnit\Framework\TestCase;

class ProviderPricePriceTest extends TestCase
{
    public function testUpdate(): void
    {
        $providerPrice = (new ProviderPriceBuilder())->build();
        $superProviderPrice = (new ProviderPriceBuilder('Super'))->build();
        $creater = new Creater('Toyota', 'Тойота', true, 'shopTable', null, null);
        $price = new Price(
            'tab',
            ',',
            '1',
            '2',
            'info@honda.ru',
            'domen@hh.ru',
            true,
            false,
            'rg',
        null
        );
        $providerPrice->updatePrice($price, $superProviderPrice, $creater);

        $this->assertEquals('tab', $providerPrice->getPrice()->getRazd());
        $this->assertEquals(',', $providerPrice->getPrice()->getRazdDecimal());
        $this->assertEquals('1', $providerPrice->getPrice()->getPrice());
        $this->assertEquals('2', $providerPrice->getPrice()->getPriceCopy());
        $this->assertEquals('info@honda.ru', $providerPrice->getPrice()->getPriceEmail());
        $this->assertEquals('domen@hh.ru', $providerPrice->getPrice()->getEmailFrom());
        $this->assertTrue($providerPrice->getPrice()->isNotCheckExt());
        $this->assertFalse($providerPrice->getPrice()->isUpdate());
        $this->assertEquals('rg', $providerPrice->getPrice()->getRgValue());
        $this->assertEquals('', $providerPrice->getPrice()->getPriceadd());
        $this->assertEquals($superProviderPrice, $providerPrice->getSuperProviderPrice());
        $this->assertEquals($creater, $providerPrice->getCreater());
        $this->assertEquals("\t", $providerPrice->getPrice()->getRazdForUpload());
    }
}