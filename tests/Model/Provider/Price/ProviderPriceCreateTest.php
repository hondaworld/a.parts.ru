<?php

namespace App\Tests\Model\Provider\Price;

use App\Model\Finance\Entity\Currency\Currency;
use App\Model\Provider\Entity\Group\ProviderPriceGroup;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class ProviderPriceCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $user = (new UserBuilder())->build();
        $providerPriceGroup = new ProviderPriceGroup();
        $provider = new Provider('Тестовый поставщик', $user, new ZapSklad('Склад', 'Склад', true, '0', null, false), '0', false);
        $name = 'Прайс поставщика';
        $description = '';
        $srok = '1-2 дн';
        $srokInDays = 1;
        $currency = new Currency(
            1,
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
        $koef = 1;
        $providerPrice = new ProviderPrice(
            $providerPriceGroup,
            $provider,
            $name,
            $description,
            $srok,
            $srokInDays,
            $currency,
            $koef,
            12.45,
            3.6,
            2,
            2.4,
            1,
            false
        );

        $this->assertEquals($providerPriceGroup, $providerPrice->getGroup());
        $this->assertEquals($provider, $providerPrice->getProvider());
        $this->assertEquals($name, $providerPrice->getName());
        $this->assertEquals($description, $providerPrice->getDescription());
        $this->assertEquals($srok, $providerPrice->getSrok());
        $this->assertEquals($srokInDays, $providerPrice->getSrokInDays());
        $this->assertEquals($currency, $providerPrice->getCurrency());
        $this->assertEquals($koef, $providerPrice->getKoef());
        $this->assertEquals(12.45, $providerPrice->getCurrencyOwn());
        $this->assertEquals(3.6, $providerPrice->getDeliveryForWeight());
        $this->assertEquals(2, $providerPrice->getDeliveryInPercent());
        $this->assertEquals(2.4, $providerPrice->getDiscount());
        $this->assertEquals(1, $providerPrice->getDaysofchanged());
        $this->assertFalse($providerPrice->isClientsHide());
    }
}