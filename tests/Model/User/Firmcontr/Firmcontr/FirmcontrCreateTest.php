<?php

namespace App\Tests\Model\User\Firmcontr\Firmcontr;

use App\Model\Beznal\Entity\Bank\Bank;
use App\Model\User\Entity\FirmContr\Address;
use App\Model\User\Entity\FirmContr\FirmContr;
use App\Model\User\Entity\FirmContr\Ur;
use App\Tests\Builder\Contact\TownBuilder;
use PHPUnit\Framework\TestCase;

class FirmcontrCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $bank = new Bank('123456', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $address = new Address('123456', 'Плещеева', '8', '1', '50');
        $ur = new Ur('Контрагент', '770012343', '770000001', '15484844989', '1545465454', true);
        $town = (new TownBuilder())->build();

        $firmContr = new FirmContr($ur, $town, $address, '8910465555551', '84995151515151', 'info@domen.ru', $bank, '1548489487878');

        $this->assertEquals($ur, $firmContr->getUr());
        $this->assertEquals($town, $firmContr->getTown());
        $this->assertEquals($address, $firmContr->getAddress());
        $this->assertEquals($bank, $firmContr->getBank());
        $this->assertEquals('8910465555551', $firmContr->getPhone());
        $this->assertEquals('84995151515151', $firmContr->getFax());
        $this->assertEquals('info@domen.ru', $firmContr->getEmail());
        $this->assertEquals('1548489487878', $firmContr->getRasschet());
    }

    public function testCreateNull(): void
    {
        $bank = new Bank('123456', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $address = new Address('123456', 'Плещеева', '8', '1', '50');
        $ur = new Ur('Контрагент', '770012343', '770000001', '15484844989', '1545465454', true);
        $town = (new TownBuilder())->build();

        $firmContr = new FirmContr($ur, $town, $address, null, null, null, $bank, null);

        $this->assertEquals($ur, $firmContr->getUr());
        $this->assertEquals($town, $firmContr->getTown());
        $this->assertEquals($address, $firmContr->getAddress());
        $this->assertEquals($bank, $firmContr->getBank());
        $this->assertEquals('', $firmContr->getPhone());
        $this->assertEquals('', $firmContr->getFax());
        $this->assertEquals('', $firmContr->getEmail());
        $this->assertEquals('', $firmContr->getRasschet());
    }
}