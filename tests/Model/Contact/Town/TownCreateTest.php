<?php

namespace App\Tests\Model\Contact\Town;

use App\Model\Contact\Entity\Country\Country;
use App\Model\Contact\Entity\Town\Town;
use App\Model\Contact\Entity\TownRegion\TownRegion;
use App\Model\Contact\Entity\TownType\TownType;
use PHPUnit\Framework\TestCase;

class TownCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $type = new TownType('г.', 'город');
        $country = new Country('Страна', "123");
        $region = new TownRegion($country, 'Регион', 2);
        $town = new Town($region, $type, 'Москва', 'МСК', 'Москва док', 2, false);

        $this->assertEquals($region, $town->getRegion());
        $this->assertEquals($type, $town->getType());
        $this->assertEquals('Москва', $town->getName());
        $this->assertEquals('МСК', $town->getNameShort());
        $this->assertEquals('Москва док', $town->getNameDoc());
        $this->assertEquals(2, $town->getDaysFromMoscow());
        $this->assertFalse($town->getIsFree());
    }
}