<?php

namespace App\Tests\Model\Provider\Invoice;

use App\Model\Provider\Entity\ProviderInvoice\Num;
use PHPUnit\Framework\TestCase;

class ProviderInvoiceNumTest extends TestCase
{
    public function testUpdate(): void
    {
        $num = new Num(
            0,
            1,
            2,
            3,
            4,
            5,
            6,
            7,
        );

        $this->assertEquals(0, $num->getNumber());
        $this->assertEquals(1, $num->getNumberType());
        $this->assertEquals(2, $num->getNumberRazd());
        $this->assertEquals(3, $num->getPrice());
        $this->assertEquals(4, $num->getSumm());
        $this->assertEquals(5, $num->getQuantity());
        $this->assertEquals(6, $num->getGtd());
        $this->assertEquals(7, $num->getCountry());
    }

    public function testUpdateNull(): void
    {
        $num = new Num(
            null,
            null,
            null,
            null,
            null,
            null,
            null,
            null,
        );

        $this->assertEquals('', $num->getNumber());
        $this->assertEquals(0, $num->getNumberType());
        $this->assertEquals('', $num->getNumberRazd());
        $this->assertEquals('', $num->getPrice());
        $this->assertEquals('', $num->getSumm());
        $this->assertEquals('', $num->getQuantity());
        $this->assertEquals('', $num->getGtd());
        $this->assertEquals('', $num->getCountry());
    }
}