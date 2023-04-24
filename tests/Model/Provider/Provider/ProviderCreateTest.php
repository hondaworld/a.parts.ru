<?php

namespace App\Tests\Model\Provider\Provider;

use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class ProviderCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $user = (new UserBuilder())->build();
        $zapSklad = new ZapSklad('Склад', 'Склад', true, '0', null, false);
        $provider = new Provider('Поставщик', $user, $zapSklad, '22,56', false);

        $this->assertEquals('Поставщик', $provider->getName());
        $this->assertEquals($user, $provider->getUser());
        $this->assertEquals($zapSklad, $provider->getZapSklad());
        $this->assertEquals(22.56, $provider->getKoefDealer());
        $this->assertFalse($provider->isDealer());
    }
}