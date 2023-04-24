<?php

namespace App\Tests\Model\Contact\Contact;

use App\Model\Contact\Entity\Contact\Address;
use App\Model\Contact\Entity\Contact\Contact;
use App\Model\Firm\Entity\Firm\Firm;
use App\Tests\Builder\Contact\TownBuilder;
use PHPUnit\Framework\TestCase;

class ByFirmCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $town = (new TownBuilder())->build();
        $address = new Address('123456', 'Плещеева', '8', '1', '50');
        $firm = $this->createMock(Firm::class);
        $contact = new Contact($firm, $town, $address, '+79104651911', '+79104555555', '+7948484848', 'https://www.ru', 'email@domen.ru', 'Описание', false, true);

        $this->assertEquals($firm, $contact->getFirm());
        $this->assertNull($contact->getUser());
        $this->assertNull($contact->getManager());
        $this->assertEquals($town, $contact->getTown());
        $this->assertEquals($address, $contact->getAddress());
        $this->assertEquals('+79104651911', $contact->getPhone());
        $this->assertEquals('+79104555555', $contact->getPhonemob());
        $this->assertEquals('+7948484848', $contact->getFax());
        $this->assertEquals('https://www.ru', $contact->getHttp());
        $this->assertEquals('email@domen.ru', $contact->getEmail());
        $this->assertEquals('Описание', $contact->getDescription());
        $this->assertFalse($contact->getIsUr());
        $this->assertTrue($contact->isMain());
    }

    public function testCreateNull(): void
    {
        $town = (new TownBuilder())->build();
        $address = new Address('123456', 'Плещеева', '8', '1', '50');
        $firm = $this->createMock(Firm::class);
        $contact = new Contact($firm, $town, $address, null, null, null, null, null, null, false, true);

        $this->assertEquals($firm, $contact->getFirm());
        $this->assertNull($contact->getUser());
        $this->assertNull($contact->getManager());
        $this->assertEquals($town, $contact->getTown());
        $this->assertEquals($address, $contact->getAddress());
        $this->assertEquals('', $contact->getPhone());
        $this->assertEquals('', $contact->getPhonemob());
        $this->assertEquals('', $contact->getFax());
        $this->assertEquals('', $contact->getHttp());
        $this->assertEquals('', $contact->getEmail());
        $this->assertEquals('', $contact->getDescription());
        $this->assertFalse($contact->getIsUr());
        $this->assertTrue($contact->isMain());
    }
}