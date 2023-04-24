<?php

namespace App\Tests\Model\Card\ZapCard\Number;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use App\Tests\Builder\Card\ZapCardBuilder;
use PHPUnit\Framework\TestCase;

class ZapCardNumberTest extends TestCase
{
    public function testNumber(): void
    {
        $zapCard = (new ZapCardBuilder())->build();

        $creater = new Creater('Toyota', 'Тойота', true, 'shopPrice2', null, null);
        $number = new DetailNumber('123411PAL');

        $zapCard->updateNumber($number, $creater);
        $this->assertTrue($zapCard->getNumber()->isEqual($number));
        $this->assertEquals($creater, $zapCard->getCreater());
    }
}