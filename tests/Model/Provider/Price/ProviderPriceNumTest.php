<?php

namespace App\Tests\Model\Provider\Price;

use App\Model\Provider\Entity\Price\Num;
use App\Tests\Builder\Provider\ProviderPriceBuilder;
use PHPUnit\Framework\TestCase;

class ProviderPriceNumTest extends TestCase
{
    public function testUpdate(): void
    {
        $price = (new ProviderPriceBuilder())->build();
        $num = new Num(
            0,
            1,
            2,
            3,
            4,
            5,
            6,
        );
        $price->updateNum($num);

        $this->assertEquals(0, $price->getNum()->getCreater());
        $this->assertEquals(1, $price->getNum()->getNumber());
        $this->assertEquals(2, $price->getNum()->getPrice());
        $this->assertEquals(3, $price->getNum()->getQuantity());
        $this->assertEquals(4, $price->getNum()->getName());
        $this->assertEquals(5, $price->getNum()->getRg());
        $this->assertEquals(6, $price->getNum()->getCreaterAdd());

        $this->assertEquals(6, $price->getNum()->getMaxCol());

        $this->assertEquals('creater', $price->getNum()->getNameFromColNum(0));
        $this->assertEquals('number', $price->getNum()->getNameFromColNum(1));
        $this->assertEquals('price', $price->getNum()->getNameFromColNum(2));
        $this->assertEquals('quantity', $price->getNum()->getNameFromColNum(3));
        $this->assertEquals('name', $price->getNum()->getNameFromColNum(4));
        $this->assertEquals('rg', $price->getNum()->getNameFromColNum(5));
        $this->assertEquals('creater_add', $price->getNum()->getNameFromColNum(6));

        $this->assertEquals('Производитель', $price->getNum()->getLabelFromColNum(0));
        $this->assertEquals('Номер', $price->getNum()->getLabelFromColNum(1));
        $this->assertEquals('Цена', $price->getNum()->getLabelFromColNum(2));
        $this->assertEquals('Количество', $price->getNum()->getLabelFromColNum(3));
        $this->assertEquals('Наименование', $price->getNum()->getLabelFromColNum(4));
        $this->assertEquals('RG', $price->getNum()->getLabelFromColNum(5));
        $this->assertEquals('Britpart', $price->getNum()->getLabelFromColNum(6));
    }

    public function testUpdateNull(): void
    {
        $price = (new ProviderPriceBuilder())->build();
        $num = new Num(
            null,
            null,
            null,
            null,
            null,
            null,
            null,
        );
        $price->updateNum($num);

        $this->assertEquals('', $price->getNum()->getCreater());
        $this->assertEquals('', $price->getNum()->getNumber());
        $this->assertEquals('', $price->getNum()->getPrice());
        $this->assertEquals('', $price->getNum()->getQuantity());
        $this->assertEquals('', $price->getNum()->getName());
        $this->assertEquals('', $price->getNum()->getRg());
        $this->assertEquals('', $price->getNum()->getCreaterAdd());

        $this->assertEquals(0, $price->getNum()->getMaxCol());

        $this->assertEquals('', $price->getNum()->getNameFromColNum(0));
        $this->assertEquals('', $price->getNum()->getNameFromColNum(1));
        $this->assertEquals('', $price->getNum()->getNameFromColNum(2));
        $this->assertEquals('', $price->getNum()->getNameFromColNum(3));
        $this->assertEquals('', $price->getNum()->getNameFromColNum(4));
        $this->assertEquals('', $price->getNum()->getNameFromColNum(5));
        $this->assertEquals('', $price->getNum()->getNameFromColNum(6));

        $this->assertEquals('', $price->getNum()->getLabelFromColNum(0));
        $this->assertEquals('', $price->getNum()->getLabelFromColNum(1));
        $this->assertEquals('', $price->getNum()->getLabelFromColNum(2));
        $this->assertEquals('', $price->getNum()->getLabelFromColNum(3));
        $this->assertEquals('', $price->getNum()->getLabelFromColNum(4));
        $this->assertEquals('', $price->getNum()->getLabelFromColNum(5));
        $this->assertEquals('', $price->getNum()->getLabelFromColNum(6));
    }
}