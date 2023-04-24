<?php

namespace App\Tests\Model\Detail\Weight;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Detail\Entity\Weight\Weight;
use PHPUnit\Framework\TestCase;

class WeightCreateTest extends TestCase
{
    public function  testCreate(): void
    {
        $creater = new Creater('Honda', 'Хонда', true, 'shopPrice1', null, null);
        $number = new DetailNumber('15400PLMA03');
        $weight = new Weight($number, $creater, '22.54', true);

        $this->asserttrue($weight->getNumber()->isEqual($number));
        $this->assertEquals($creater, $weight->getCreater());
        $this->assertEquals('22.54', $weight->getWeight());
        $this->assertTrue($weight->getWeightIsReal());
    }
}