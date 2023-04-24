<?php

namespace App\Tests\Model\Provider\Provider;

use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class ProviderUpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $user = (new UserBuilder())->build();
        $user1 = (new UserBuilder('+794949494'))->build();
        $zapSklad = new ZapSklad('Склад', 'Склад', true, '0', null, false);
        $zapSklad1 = new ZapSklad('Склад другой', 'Склад', true, '0', null, false);
        $provider = new Provider('Поставщик', $user, $zapSklad, '22,56', false);

        $provider->update('Поставщик новый', $user1, $zapSklad1, '12.78', true);

        $this->assertEquals('Поставщик новый', $provider->getName());
        $this->assertEquals($user1, $provider->getUser());
        $this->assertEquals($zapSklad1, $provider->getZapSklad());
        $this->assertEquals(12.78, $provider->getKoefDealer());
        $this->assertTrue($provider->isDealer());
    }
}