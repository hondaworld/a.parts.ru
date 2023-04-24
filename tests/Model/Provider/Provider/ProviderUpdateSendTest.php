<?php

namespace App\Tests\Model\Provider\Provider;

use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class ProviderUpdateSendTest extends TestCase
{
    public function testUpdateSend(): void
    {
        $user = (new UserBuilder())->build();
        $zapSklad = new ZapSklad('Склад', 'Склад', true, '0', null, false);
        $provider = new Provider('Поставщик', $user, $zapSklad, '22,56', false);

        $provider->updateSend(true, ["1", "2", "5"], '20:0');

        $this->assertTrue($provider->isIncomeOrderAutoSend());
        $this->assertEquals(["1", "2", "5"], $provider->getIncomeOrderWeekDays());
        $this->assertEquals(["ПН", "ВТ", "ПТ"], $provider->getIncomeOrderWeekDaysValues());
        $this->assertEquals('20:0', $provider->getIncomeOrderTime());

        $provider->updateSend(true, ["1", "2", "3", "4", "5", "6", "7"], '20:0');
        $this->assertEquals(["ПН", "ВТ", "СР", "ЧТ", "ПТ", "СБ", "ВС"], $provider->getIncomeOrderWeekDaysValues());

        $provider->updateSend(true, ["1", "2", "3", "4", "5", "6", "7", "8"], '20:0');
        $this->assertEquals(["ПН", "ВТ", "СР", "ЧТ", "ПТ", "СБ", "ВС"], $provider->getIncomeOrderWeekDaysValues());
    }

    public function testUpdateSendNull(): void
    {
        $user = (new UserBuilder())->build();
        $zapSklad = new ZapSklad('Склад', 'Склад', true, '0', null, false);
        $provider = new Provider('Поставщик', $user, $zapSklad, '22,56', false);

        $provider->updateSend(false, null, null);

        $this->assertFalse($provider->isIncomeOrderAutoSend());
        $this->assertEquals([], $provider->getIncomeOrderWeekDays());
        $this->assertEquals([], $provider->getIncomeOrderWeekDaysValues());
        $this->assertEquals('', $provider->getIncomeOrderTime());
    }
}