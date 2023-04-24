<?php

namespace App\Tests\Model\Contact\Town;

use App\Model\Contact\Entity\Country\Country;
use PHPUnit\Framework\TestCase;

class CountryCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $country = new Country('Страна', "123");

        $this->assertEquals('Страна', $country->getName());
        $this->assertEquals('123', $country->getCode());
    }
}