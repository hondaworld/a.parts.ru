<?php

namespace App\Tests\Model\Card\ZapCard\Stock;

use App\Model\Card\Entity\Stock\ZapCardStock;
use App\Tests\Builder\Provider\ProviderBuilder;
use PHPUnit\Framework\TestCase;

class ZapCardStockProvidersTest extends TestCase
{
    public function testProviders(): void
    {
        $zapCadStock = new ZapCardStock('Название', 'Текст');

        $provider1 = (new ProviderBuilder('Провайдер 1'))->build();
        $provider2 = (new ProviderBuilder('Провайдер 2'))->build();

        $zapCadStock->assignProvider($provider1);
        $zapCadStock->assignProvider($provider2);

        $this->assertCount(2, $zapCadStock->getProviders());

        $this->assertEquals($provider1, $zapCadStock->getProviders()[0]);
        $this->assertEquals($provider2, $zapCadStock->getProviders()[1]);

        $zapCadStock->cleaProviders();

        $this->assertCount(0, $zapCadStock->getProviders());
    }
}