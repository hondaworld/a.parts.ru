<?php

namespace App\Tests\Model\Provider\Invoice;

use App\Model\Provider\Entity\ProviderInvoice\Num;
use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice;
use App\Model\Shop\Entity\DeleteReason\DeleteReason;
use App\Tests\Builder\Provider\ProviderBuilder;
use PHPUnit\Framework\TestCase;

class ProviderInvoiceTest extends TestCase
{
    public function testCreate(): void
    {
        $provider = (new ProviderBuilder())->build();
        $deleteReason = new DeleteReason('Причина удаления', false);
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

        $providerInvoice = new ProviderInvoice($provider, '5,6,7', 8, 10, $deleteReason, '12', 'info@honda.ru', 'domen@hh.ru', '124', $num);

        $this->assertEquals($provider, $providerInvoice->getProvider());
        $this->assertEquals('5,6,7', $providerInvoice->getStatusFrom());
        $this->assertEquals(8, $providerInvoice->getStatusTo());
        $this->assertEquals(10, $providerInvoice->getStatusNone());
        $this->assertEquals($deleteReason, $providerInvoice->getDeleteReason());
        $this->assertEquals('12', $providerInvoice->getPrice());
        $this->assertEquals('info@honda.ru', $providerInvoice->getPriceEmail());
        $this->assertEquals('domen@hh.ru', $providerInvoice->getEmailFrom());
        $this->assertEquals('124', $providerInvoice->getPriceAdd());
        $this->assertEquals($num, $providerInvoice->getNum());

        $this->assertEquals(0, $providerInvoice->getNum()->getNumber());
        $this->assertEquals(1, $providerInvoice->getNum()->getNumberType());
        $this->assertEquals(2, $providerInvoice->getNum()->getNumberRazd());
        $this->assertEquals(3, $providerInvoice->getNum()->getPrice());
        $this->assertEquals(4, $providerInvoice->getNum()->getSumm());
        $this->assertEquals(5, $providerInvoice->getNum()->getQuantity());
        $this->assertEquals(6, $providerInvoice->getNum()->getGtd());
        $this->assertEquals(7, $providerInvoice->getNum()->getCountry());
    }

    public function testUpdate(): void
    {
        $provider = (new ProviderBuilder())->build();
        $deleteReason = new DeleteReason('Причина удаления', false);
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

        $providerInvoice = new ProviderInvoice($provider, '5,6,7', 8, 10, $deleteReason, '12', 'info@honda.ru', 'domen@hh.ru', '124', $num);

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

        $deleteReason = new DeleteReason('Причина удаления 1', false);
        $providerInvoice->update('2,3,7', 9, 11, $deleteReason, 'sss', 'info1@honda.ru', 'domen1@hh.ru', '1242', $num);


        $this->assertEquals($provider, $providerInvoice->getProvider());
        $this->assertEquals('2,3,7', $providerInvoice->getStatusFrom());
        $this->assertEquals(9, $providerInvoice->getStatusTo());
        $this->assertEquals(11, $providerInvoice->getStatusNone());
        $this->assertEquals($deleteReason, $providerInvoice->getDeleteReason());
        $this->assertEquals('sss', $providerInvoice->getPrice());
        $this->assertEquals('info1@honda.ru', $providerInvoice->getPriceEmail());
        $this->assertEquals('domen1@hh.ru', $providerInvoice->getEmailFrom());
        $this->assertEquals('1242', $providerInvoice->getPriceAdd());
        $this->assertEquals($num, $providerInvoice->getNum());

        $this->assertEquals('', $providerInvoice->getNum()->getNumber());
        $this->assertEquals(0, $providerInvoice->getNum()->getNumberType());
        $this->assertEquals('', $providerInvoice->getNum()->getNumberRazd());
        $this->assertEquals('', $providerInvoice->getNum()->getPrice());
        $this->assertEquals('', $providerInvoice->getNum()->getSumm());
        $this->assertEquals('', $providerInvoice->getNum()->getQuantity());
        $this->assertEquals('', $providerInvoice->getNum()->getGtd());
        $this->assertEquals('', $providerInvoice->getNum()->getCountry());
    }
}