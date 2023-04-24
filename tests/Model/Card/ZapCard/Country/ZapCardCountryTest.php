<?php

namespace App\Tests\Model\Card\ZapCard\Country;

use App\Model\Contact\Entity\Country\Country;
use App\Tests\Builder\Card\ZapCardBuilder;
use PHPUnit\Framework\TestCase;

class ZapCardCountryTest extends TestCase
{
    public function testCountry(): void
    {
        $zapCard = (new ZapCardBuilder())->build();

        $country = new Country('Страна', '123');

        $zapCard->updateCountry($country);
        $this->assertEquals($country, $zapCard->getCountry());
        $zapCard->updateCountry(null);
        $this->assertNull($zapCard->getCountry());
    }
}