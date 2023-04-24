<?php

namespace App\Tests\Builder\Card;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Measure\EdIzm;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Shop\Entity\ShopType\ShopType;
use App\Model\Sklad\Entity\PriceGroup\PriceGroup;
use App\Model\Sklad\Entity\PriceList\PriceList;

class ZapCardBuilder
{
    private ShopType $shopType;
    private PriceGroup $priceGroup;
    private EdIzm $edIzm;
    private string $number = '';
    private Creater $creater;

    public function __construct(string $number = '15400PLMA03')
    {
        $this->shopType = new ShopType('Запчасти');
        $this->priceGroup = new PriceGroup(new PriceList('Прайс-лист', null, false, false), 'Группа прайс-листов', false);
        $this->edIzm = new EdIzm('шт.', 'шт.', 1);
        $this->number = $number;
        $this->creater = new Creater('Honda', 'Хонда', true, 'shopTable', null, null);
    }

    public function build(): ZapCard
    {
        $zapCard = new ZapCard(
            new DetailNumber($this->number),
            $this->creater,
            $this->shopType,
            null,
            null,
            null,
            $this->priceGroup,
            $this->edIzm
        );

        return $zapCard;
    }
}