<?php

namespace App\Tests\Builder\User;

use App\Model\Beznal\Entity\Bank\Bank;
use App\Model\Contact\Entity\Town\Town;
use App\Model\User\Entity\FirmContr\Address;
use App\Model\User\Entity\FirmContr\FirmContr;
use App\Model\User\Entity\FirmContr\Ur;
use App\Tests\Builder\Contact\TownBuilder;

class FirmcontrBuilder
{
    private string $name;
    private Bank $bank;
    private Address $address;
    private Ur $ur;
    private Town $town;

    public function __construct(string $name = 'Контрагент')
    {
        $this->name = $name;
        $this->bank = new Bank('123456', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $this->address = new Address('123456', 'Плещеева', '8', '1', '50');
        $this->ur = new Ur($this->name, '770012343', '770000001', '15484844989', '1545465454', true);
        $this->town = (new TownBuilder())->build();
    }

    public function build(): FirmContr
    {
        $firmContr = new FirmContr($this->ur, $this->town, $this->address, '8910465555551', '84995151515151', 'info@domen.ru', $this->bank, '1548489487878');

        return $firmContr;
    }
}