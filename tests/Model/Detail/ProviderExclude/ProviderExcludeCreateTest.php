<?php

namespace App\Tests\Model\Detail\ProviderExclude;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Detail\Entity\ProviderExclude\DetailProviderExclude;
use PHPUnit\Framework\TestCase;

class ProviderExcludeCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $creater = new Creater('Honda', 'Хонда', true, 'shopPrice1', null, null);
        $number = new DetailNumber('15400PLMA03');

        $providerExclude = new DetailProviderExclude($number, $creater, 3, 'Комментарий');

        $this->assertTrue($providerExclude->getNumber()->isEqual($number));
        $this->assertEquals($creater, $providerExclude->getCreater());
        $this->assertEquals(3, $providerExclude->getProviderID());
        $this->assertEquals('Комментарий', $providerExclude->getComment());
    }

    public function testUpdate(): void
    {
        $creater = new Creater('Honda', 'Хонда', true, 'shopPrice1', null, null);
        $number = new DetailNumber('15400PLMA03');

        $providerExclude = new DetailProviderExclude($number, $creater, 3, 'Комментарий');

        $this->assertTrue($providerExclude->getNumber()->isEqual($number));
        $this->assertEquals($creater, $providerExclude->getCreater());
        $this->assertEquals(3, $providerExclude->getProviderID());
        $this->assertEquals('Комментарий', $providerExclude->getComment());

        $providerExclude->update('Новый комментарий');
        $this->assertEquals('Новый комментарий', $providerExclude->getComment());

        $providerExclude->update(null);
        $this->assertEquals('', $providerExclude->getComment());
    }
}