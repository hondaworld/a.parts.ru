<?php

namespace App\Tests\Model\Card\ZapCard;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Measure\EdIzm;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Shop\Entity\ShopType\ShopType;
use App\Model\Sklad\Entity\PriceGroup\PriceGroup;
use App\Model\Sklad\Entity\PriceList\PriceList;
use PHPUnit\Framework\TestCase;

class ZapCardCreateTest extends TestCase
{
    public function testDetailNumber(): void
    {
        $number = new DetailNumber('154\'0"0"-pl=M.A-03 ');
        $this->assertEquals('15400PLMA03', $number->getValue());
    }

    public function testCreate(): void
    {
        $shopType = new ShopType('Запчасти');
        $priceGroup = new PriceGroup(new PriceList('Прайс-лист', null, false, false), 'Группа прайс-листов', false);
        $edIzm = new EdIzm('шт.', 'шт.', 1);
        $number = new DetailNumber('15400PLMA03');
        $creater = new Creater('Honda', 'Хонда', true, 'shopTable', null, null);
        $zapCard = new ZapCard(
            $number,
            $creater,
            $shopType,
            null,
            null,
            null,
            $priceGroup,
            $edIzm
        );
        $this->assertEquals('15400PLMA03', $zapCard->getNumber()->getValue());
    }
}