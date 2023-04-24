<?php

namespace App\Tests\Model\Sklad\PriceList;

use App\Model\Sklad\Entity\PriceList\PriceList;
use App\Model\User\Entity\Opt\Opt;
use PHPUnit\Framework\TestCase;

class PriceListOptTest extends TestCase
{
    public function testAssign(): void
    {
        $priceList = new PriceList('Название прайс-листа', 1, true, true);

        $opt1 = new Opt('Розница', 1);
        $opt2 = new Opt('Опт', 2);

        $priceList->assignProfit($opt1, '2');
        $priceList->assignProfit($opt2, '5');

        $this->assertCount(2, $priceList->getProfits());
        $profit1 = $priceList->getProfits()[0];
        $profit2 = $priceList->getProfits()[1];

        $this->assertEquals($opt1, $profit1->getOpt());
        $this->assertEquals($opt2, $profit2->getOpt());
        $this->assertEquals(2, $profit1->getProfit());
        $this->assertEquals(5, $profit2->getProfit());

        $priceList->clearProfits();

        $this->assertCount(0, $priceList->getProfits());
    }
}