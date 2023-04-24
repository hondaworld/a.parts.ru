<?php

namespace App\Tests\Model\Contact\Address;

use App\Model\Contact\Entity\Contact\Address;
use App\Tests\Builder\Contact\TownBuilder;
use PHPUnit\Framework\TestCase;

class CreateAddressTest extends TestCase
{
    public function testCreate(): void
    {
        $town = (new TownBuilder())->build();
        $address = new Address('123456', 'Плещеева', '8', '1', '50');

        $this->assertEquals('123456', $address->getZip());
        $this->assertEquals('Плещеева', $address->getStreet());
        $this->assertEquals('8', $address->getHouse());
        $this->assertEquals('1', $address->getStr());
        $this->assertEquals('50', $address->getKv());
    }

    public function testCreateNull(): void
    {
        $town = (new TownBuilder())->build();
        $address = new Address(null, null, null, null, null);

        $this->assertEquals('', $address->getZip());
        $this->assertEquals('', $address->getStreet());
        $this->assertEquals('', $address->getHouse());
        $this->assertEquals('', $address->getStr());
        $this->assertEquals('', $address->getKv());
    }
}