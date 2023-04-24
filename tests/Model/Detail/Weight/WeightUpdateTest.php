<?php

namespace App\Tests\Model\Detail\Weight;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Detail\Entity\Weight\Weight;
use PHPUnit\Framework\TestCase;

class WeightUpdateTest extends TestCase
{
    public function  testUpdate(): void
    {
        $creater = new Creater('Honda', 'Хонда', true, 'shopPrice1', null, null);
        $number = new DetailNumber('15400PLMA03');
        $weight = new Weight($number, $creater, '22.54', true);

        $weight->update('12.34', false);

        $this->asserttrue($weight->getNumber()->isEqual($number));
        $this->assertEquals($creater, $weight->getCreater());
        $this->assertEquals('12.34', $weight->getWeight());
        $this->assertFalse($weight->getWeightIsReal());
    }

    public function  testUpdateWeight(): void
    {
        $creater = new Creater('Honda', 'Хонда', true, 'shopPrice1', null, null);
        $number = new DetailNumber('15400PLMA03');
        $weight = new Weight($number, $creater, '22.54', true);

        $weight->updateWeight('12.34');

        $this->asserttrue($weight->getNumber()->isEqual($number));
        $this->assertEquals($creater, $weight->getCreater());
        $this->assertEquals('12.34', $weight->getWeight());
        $this->assertTrue($weight->getWeightIsReal());
    }
}