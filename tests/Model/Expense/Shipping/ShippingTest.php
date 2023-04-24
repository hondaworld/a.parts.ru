<?php

namespace App\Tests\Model\Expense\Shipping;

use App\Model\Expense\Entity\ShippingStatus\ShippingStatus;
use App\Model\Shop\Entity\DeliveryTk\DeliveryTk;
use App\Tests\Builder\Expense\ExpenseDocumentBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class ShippingTest extends TestCase
{
    public function testCreate(): void
    {
        $user = (new UserBuilder())->build();
        $expenseDocument = (new ExpenseDocumentBuilder())->withExpUser($user, null, null)->build();
        $shippingStatus = $this->createMock(ShippingStatus::class);
        $expenseDocument->assignShipping($shippingStatus);

        $this->assertCount(1, $expenseDocument->getShippings());

        foreach ($expenseDocument->getShippings() as $item) {
            $this->assertEquals($shippingStatus, $item->getStatus());
            $this->assertEquals($expenseDocument->getExpUser(), $item->getUser());
        }
    }

    public function testGetOrCreate(): void
    {
        $expenseDocument = (new ExpenseDocumentBuilder())->build();
        $shippingStatus = $this->createMock(ShippingStatus::class);
        $shippingStatus1 = $this->createMock(ShippingStatus::class);
        $shipping = $expenseDocument->getOrCreateShipping($shippingStatus);
        $shipping1 = $expenseDocument->getOrCreateShipping($shippingStatus1);

        $this->assertEquals($shippingStatus, $shipping->getStatus());
        $this->assertEquals($shippingStatus, $shipping1->getStatus());

        $this->assertCount(1, $expenseDocument->getShippings());

        foreach ($expenseDocument->getShippings() as $item) {
            $this->assertEquals($shippingStatus, $item->getStatus());
        }
    }

    public function testUpdateStatus(): void
    {
        $expenseDocument = (new ExpenseDocumentBuilder())->build();
        $shippingStatus = $this->createMock(ShippingStatus::class);
        $shippingStatus1 = $this->createMock(ShippingStatus::class);
        $shipping = $expenseDocument->getOrCreateShipping($shippingStatus);

        $shipping->updateStatus($shippingStatus1);

        foreach ($expenseDocument->getShippings() as $item) {
            $this->assertEquals($shippingStatus1, $item->getStatus());
        }
    }

    public function testUpdateNakladnaya(): void
    {
        $expenseDocument = (new ExpenseDocumentBuilder())->build();
        $shippingStatus = $this->createMock(ShippingStatus::class);
        $shipping = $expenseDocument->getOrCreateShipping($shippingStatus);

        $shipping->updateNakladnaya('12341');
        $this->assertEquals('12341', $shipping->getNakladnaya());

        $shipping->removeNakladnaya();
        $this->assertEquals('', $shipping->getNakladnaya());
    }

    public function testUpdateDelivery(): void
    {
        $expenseDocument = (new ExpenseDocumentBuilder())->build();
        $shippingStatus = $this->createMock(ShippingStatus::class);
        $deliveryTk = new DeliveryTk('Доставка', null, null);

        $shipping = $expenseDocument->getOrCreateShipping($shippingStatus);

        $d = new \DateTime('-3 days');
        $shipping->updateDelivery($d, $deliveryTk, 'HDA325435', 1);

        $this->assertEquals($d, $shipping->getDateofadded());
        $this->assertEquals($deliveryTk, $shipping->getDeliveryTk());
        $this->assertEquals('HDA325435', $shipping->getTracknumber());
        $this->assertEquals(1, $shipping->getPayType());
    }
}