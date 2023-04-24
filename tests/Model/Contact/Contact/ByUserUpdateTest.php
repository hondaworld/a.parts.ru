<?php

namespace App\Tests\Model\Contact\Contact;

use App\Model\Contact\Entity\Contact\Address;
use App\Model\Contact\Entity\Contact\Contact;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\Contact\TownBuilder;
use PHPUnit\Framework\TestCase;

class ByUserUpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $town = (new TownBuilder())->build();
        $address = new Address('123456', 'Плещеева', '8', '1', '50');
        $user = $this->createMock(User::class);
        $contact = new Contact($user, $town, $address, '+79104651911', '+79104555555', '+7948484848', 'https://www.ru', 'email@domen.ru', 'Описание', false, true);

        $town1 = (new TownBuilder('Санкт-Петербург'))->build();
        $address1 = new Address('654321', 'Санникова', '11', '1', '229');
        $contact->update($town1, $address1, '+79104651912', '+79104555556', '+7948484849', 'https://www.yandex.ru', 'email1@domen.ru', 'Описание 1', true, false);

        $this->assertEquals($town1, $contact->getTown());
        $this->assertEquals($address1, $contact->getAddress());
        $this->assertEquals('+79104651912', $contact->getPhone());
        $this->assertEquals('+79104555556', $contact->getPhonemob());
        $this->assertEquals('+7948484849', $contact->getFax());
        $this->assertEquals('https://www.yandex.ru', $contact->getHttp());
        $this->assertEquals('email1@domen.ru', $contact->getEmail());
        $this->assertEquals('Описание 1', $contact->getDescription());
        $this->assertTrue($contact->getIsUr());
        $this->assertFalse($contact->isMain());
    }

    public function testUpdateNull(): void
    {
        $town = (new TownBuilder())->build();
        $address = new Address('123456', 'Плещеева', '8', '1', '50');
        $user = $this->createMock(User::class);
        $contact = new Contact($user, $town, $address, '+79104651911', '+79104555555', '+7948484848', 'https://www.ru', 'email@domen.ru', 'Описание', false, true);


        $town1 = (new TownBuilder('Санкт-Петербург'))->build();
        $address1 = new Address('654321', 'Санникова', '11', '1', '229');
        $contact->update($town1, $address1, null, null, null, null, null, null, true, false);
        $this->assertEquals($town1, $contact->getTown());
        $this->assertEquals($address1, $contact->getAddress());
        $this->assertEquals('', $contact->getPhone());
        $this->assertEquals('', $contact->getPhonemob());
        $this->assertEquals('', $contact->getFax());
        $this->assertEquals('', $contact->getHttp());
        $this->assertEquals('', $contact->getEmail());
        $this->assertEquals('', $contact->getDescription());
        $this->assertTrue($contact->getIsUr());
        $this->assertFalse($contact->isMain());
    }
}