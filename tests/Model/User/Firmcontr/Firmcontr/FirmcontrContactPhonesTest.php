<?php

namespace App\Tests\Model\User\Firmcontr\Firmcontr;

use App\Model\Beznal\Entity\Bank\Bank;
use App\Model\User\Entity\FirmContr\Address;
use App\Model\User\Entity\FirmContr\FirmContr;
use App\Model\User\Entity\FirmContr\Ur;
use App\Tests\Builder\Contact\TownBuilder;
use PHPUnit\Framework\TestCase;

class FirmcontrContactPhonesTest extends TestCase
{
    public function testContactPhones(): void
    {
        $bank = new Bank('123456', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $address = new Address('123456', 'Плещеева', '8', '1', '50');
        $ur = new Ur('Контрагент', '770012343', '770000001', '15484844989', '1545465454', true);
        $town = (new TownBuilder())->build();

        $firmContr = new FirmContr($ur, $town, $address, '8910465555551', '84995151515151', 'info@domen.ru', $bank, '1548489487878');

        $this->assertEquals('тел.: 8910465555551, факс.: 84995151515151', $firmContr->getContactPhones());
    }

    public function testContactPhonesWithoutPhone(): void
    {
        $bank = new Bank('123456', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $address = new Address('123456', 'Плещеева', '8', '1', '50');
        $ur = new Ur('Контрагент', '770012343', '770000001', '15484844989', '1545465454', true);
        $town = (new TownBuilder())->build();

        $firmContr = new FirmContr($ur, $town, $address, null, '84995151515151', 'info@domen.ru', $bank, '1548489487878');

        $this->assertEquals('факс.: 84995151515151', $firmContr->getContactPhones());
    }

    public function testContactPhonesWithoutFax(): void
    {
        $bank = new Bank('123456', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $address = new Address('123456', 'Плещеева', '8', '1', '50');
        $ur = new Ur('Контрагент', '770012343', '770000001', '15484844989', '1545465454', true);
        $town = (new TownBuilder())->build();

        $firmContr = new FirmContr($ur, $town, $address, '8910465555551', null, 'info@domen.ru', $bank, '1548489487878');

        $this->assertEquals('тел.: 8910465555551', $firmContr->getContactPhones());
    }

    public function testContactPhonesWithoutPhoneAndFax(): void
    {
        $bank = new Bank('123456', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $address = new Address('123456', 'Плещеева', '8', '1', '50');
        $ur = new Ur('Контрагент', '770012343', '770000001', '15484844989', '1545465454', true);
        $town = (new TownBuilder())->build();

        $firmContr = new FirmContr($ur, $town, $address, null, null, 'info@domen.ru', $bank, '1548489487878');

        $this->assertEquals('', $firmContr->getContactPhones());
    }
}