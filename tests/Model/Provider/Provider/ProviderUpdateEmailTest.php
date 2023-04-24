<?php

namespace App\Tests\Model\Provider\Provider;

use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class ProviderUpdateEmailTest extends TestCase
{
    public function testUpdateEmail(): void
    {
        $user = (new UserBuilder())->build();
        $zapSklad = new ZapSklad('Склад', 'Склад', true, '0', null, false);
        $provider = new Provider('Поставщик', $user, $zapSklad, '22,56', false);

        $provider->updateEmail(2, 'subject1', 'текст 1', 'subject5', 'текст 5', 'info@honda.ru', true);

        $this->assertEquals(2, $provider->getIncomeOrderNumber());
        $this->assertEquals('subject1', $provider->getIncomeOrderSubject());
        $this->assertEquals('текст 1', $provider->getIncomeOrderText());
        $this->assertEquals('subject5', $provider->getIncomeOrderSubject5());
        $this->assertEquals('текст 5', $provider->getIncomeOrderText5());
        $this->assertEquals('info@honda.ru', $provider->getIncomeOrderEmail());
        $this->assertTrue($provider->isIncomeOrder());
    }

    public function testUpdateEmailNull(): void
    {
        $user = (new UserBuilder())->build();
        $zapSklad = new ZapSklad('Склад', 'Склад', true, '0', null, false);
        $provider = new Provider('Поставщик', $user, $zapSklad, '22,56', false);

        $provider->updateEmail(null, null, null, null, null, null, false);

        $this->assertEquals(1, $provider->getIncomeOrderNumber());
        $this->assertEquals('', $provider->getIncomeOrderSubject());
        $this->assertEquals('', $provider->getIncomeOrderText());
        $this->assertEquals('', $provider->getIncomeOrderSubject5());
        $this->assertEquals('', $provider->getIncomeOrderText5());
        $this->assertEquals('', $provider->getIncomeOrderEmail());
        $this->assertFalse($provider->isIncomeOrder());
    }
}