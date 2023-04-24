<?php

namespace App\Tests\Model\Provider\Price;

use App\Model\Finance\Entity\Currency\Currency;
use App\Model\Provider\Entity\Group\ProviderPriceGroup;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class ProviderPriceUpdateTest extends TestCase
{
    public function testUpdate(): void
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

        $currency1 = new Currency(
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

        $providerPrice->update($providerPriceGroup, $provider, 'Новый прайс', 'Описание нового прайса', '3-4 дн', 3, $currency1, 11.2, 13.5, 4.6,
            5,
            2.8,
            2,
            true);

        $this->assertEquals($providerPriceGroup, $providerPrice->getGroup());
        $this->assertEquals($provider, $providerPrice->getProvider());
        $this->assertEquals('Новый прайс', $providerPrice->getName());
        $this->assertEquals('Описание нового прайса', $providerPrice->getDescription());
        $this->assertEquals('3-4 дн', $providerPrice->getSrok());
        $this->assertEquals(3, $providerPrice->getSrokInDays());
        $this->assertEquals($currency1, $providerPrice->getCurrency());
        $this->assertEquals(11.2, $providerPrice->getKoef());
        $this->assertEquals(13.5, $providerPrice->getCurrencyOwn());
        $this->assertEquals(4.6, $providerPrice->getDeliveryForWeight());
        $this->assertEquals(5, $providerPrice->getDeliveryInPercent());
        $this->assertEquals(2.8, $providerPrice->getDiscount());
        $this->assertEquals(2, $providerPrice->getDaysofchanged());
        $this->assertTrue($providerPrice->isClientsHide());
    }
}