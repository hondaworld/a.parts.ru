<?php

namespace App\Tests\Model\User\Firmcontr\Address;

use App\Model\User\Entity\FirmContr\Address;
use PHPUnit\Framework\TestCase;

class FirmcontrAddressCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $address = new Address('123456', 'Плещеева', '8', '1', '50');

        $this->assertEquals('123456', $address->getZip());
        $this->assertEquals('Плещеева', $address->getStreet());
        $this->assertEquals('8', $address->getHouse());
        $this->assertEquals('1', $address->getStr());
        $this->assertEquals('50', $address->getKv());
        $this->assertEquals(', Плещеева, д.8, стр./корп.1, кв./оф.50', $address->getFullAddress());
    }

    public function testCreateNull(): void
    {
        $address = new Address(null, null, null, null, null);

        $this->assertEquals('', $address->getZip());
        $this->assertEquals('', $address->getStreet());
        $this->assertEquals('', $address->getHouse());
        $this->assertEquals('', $address->getStr());
        $this->assertEquals('', $address->getKv());
        $this->assertEquals('', $address->getFullAddress());
    }
}