<?php

namespace App\Tests\Model\Detail\Zamena;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Detail\Entity\Zamena\ShopZamena;
use App\Tests\Builder\Manager\ManagerBuilder;
use PHPUnit\Framework\TestCase;

class ZamenaCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $creater = new Creater('Honda', 'Хонда', true, 'shopPrice1', null, null);
        $number = new DetailNumber('15400PLMA03');
        $creater2 = new Creater('Toyota', 'Тойота', true, 'shopPrice1', null, null);
        $number2 = new DetailNumber('15400PLMA02');
        $manager = (new ManagerBuilder())->build();

        $shopZamena = new ShopZamena($number, $creater, $number2, $creater2, $manager);

        $this->assertTrue($shopZamena->getNumber()->isEqual($number));
        $this->assertEquals($creater, $shopZamena->getCreater());
        $this->assertTrue($shopZamena->getNumber2()->isEqual($number2));
        $this->assertEquals($creater2, $shopZamena->getCreater2());
        $this->assertEquals($manager, $shopZamena->getManager());
    }
}