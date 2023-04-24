<?php

namespace App\Tests\Builder\Provider;

use App\Model\Finance\Entity\Currency\Currency;
use App\Model\Provider\Entity\Group\ProviderPriceGroup;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\User\UserBuilder;

class ProviderPriceBuilder
{
    private ProviderPriceGroup $providerPriceGroup;
    private Provider $provider;
    private string $name;
    private string $description;
    private string $srok;
    private int $srokInDays;
    private Currency $currency;
    private int $koef;

    public function __construct(string $name = 'Тестовый прайс-лист')
    {
        $user = (new UserBuilder())->build();
        $this->providerPriceGroup = new ProviderPriceGroup();
        $this->provider = new Provider('Тестовый поставщик', $user, new ZapSklad('Склад', 'Склад', true, '0', null, false), '0', false);
        $this->name = $name;
        $this->description = '';
        $this->srok = '1-2 дн';
        $this->srokInDays = 1;
        $this->currency = new Currency(
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
        $this->koef = 1;
    }

    public function build(): ProviderPrice
    {
        $providerPrice = new ProviderPrice(
            $this->providerPriceGroup,
            $this->provider,
            $this->name,
            $this->description,
            $this->srok,
            $this->srokInDays,
            $this->currency,
            $this->koef,
            null,
            null,
            null,
            '',
            1,
            false
        );

        return $providerPrice;
    }
}