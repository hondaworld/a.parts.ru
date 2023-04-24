<?php

namespace App\Tests\Model\Card\ZapCard\ZapCardOpt;

use App\Model\User\Entity\Opt\Opt;
use App\Tests\Builder\Card\ZapCardBuilder;
use PHPUnit\Framework\TestCase;

class ZapCardOptTest extends TestCase
{
    public function testAssign(): void
    {
        $zapCard = (new ZapCardBuilder())->build();

        $opt = new Opt('Опт', 1);

        $zapCard->assignZapCardOpt($opt, '23.4');

        $this->assertCount(1, $zapCard->getProfits());

        $zapCardProfit = $zapCard->getProfits()[0];

        $this->assertEquals(23.4, $zapCardProfit->getProfit());
        $this->assertEquals($opt, $zapCardProfit->getOpt());

        $zapCard->clearZapCardOpt();

        $this->assertCount(0, $zapCard->getProfits());
    }
}