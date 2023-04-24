<?php

namespace App\Tests\Builder\Contact;

use App\Model\Contact\Entity\Country\Country;
use App\Model\Contact\Entity\Town\Town;
use App\Model\Contact\Entity\TownRegion\TownRegion;
use App\Model\Contact\Entity\TownType\TownType;

class TownBuilder
{
    private string $name;
    private TownType $type;
    private TownRegion $region;
    private Country $country;

    public function __construct(string $name = 'Москва')
    {
        $this->name = $name;
        $this->type = new TownType('г.', 'город');
        $this->country = new Country('Страна', "123");
        $this->region = new TownRegion($this->country, 'Регион', 2);
    }

    public function regionSameTown(): self
    {
        $clone = clone $this;
        $clone->region->update($this->region->getCountry(), $this->name, $this->region->getDaysFromMoscow());
        return $clone;
    }

    public function build(): Town
    {
        $town = new Town($this->region, $this->type, $this->name, 'МСК', 'Москва док', 2, false);

        return $town;
    }
}