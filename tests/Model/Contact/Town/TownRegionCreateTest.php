<?php

namespace App\Tests\Model\Contact\Town;

use App\Model\Contact\Entity\Country\Country;
use App\Model\Contact\Entity\TownRegion\TownRegion;
use PHPUnit\Framework\TestCase;

class TownRegionCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $country = new Country('Страна', "123");
        $region = new TownRegion($country, 'Регион', 2);

        $this->assertEquals($country, $region->getCountry());
        $this->assertEquals('Регион', $region->getName());
        $this->assertEquals(2, $region->getDaysFromMoscow());
    }
}