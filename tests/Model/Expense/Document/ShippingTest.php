<?php

namespace App\Tests\Model\Expense\Document;

use App\Model\Expense\Entity\ShippingStatus\ShippingStatus;
use App\Tests\Builder\Expense\ExpenseDocumentBuilder;
use PHPUnit\Framework\TestCase;

class ShippingTest extends TestCase
{
    public function testPick(): void
    {
        $expenseDocument = (new ExpenseDocumentBuilder())->build();

        $this->assertFalse($expenseDocument->isPick());
        $expenseDocument->pickDelete();
        $this->assertFalse($expenseDocument->isPick());
    }

    public function testPicking(): void
    {
        $expenseDocument = (new ExpenseDocumentBuilder())->build();

        $expenseDocument->picking();
        $this->assertTrue($expenseDocument->isPicking());
    }

    public function testPicked(): void
    {
        $expenseDocument = (new ExpenseDocumentBuilder())->build();

        $expenseDocument->picked();
        $this->assertTrue($expenseDocument->isPicked());
    }

    public function testShipping(): void
    {
        $expenseDocument = (new ExpenseDocumentBuilder())->build();

        $status = $this->createMock(ShippingStatus::class);
        $status->method('getId')->willReturn(ShippingStatus::PICKING_STATUS);

        $shipping = $expenseDocument->getOrCreateShipping($status);

        $this->assertEquals($shipping, $expenseDocument->getOrCreateShipping($status));
    }

    public function testShippingStatus(): void
    {
        $expenseDocument = (new ExpenseDocumentBuilder())->build();

        $status = $this->createMock(ShippingStatus::class);
        $status->method('getId')->willReturn(ShippingStatus::PICKING_STATUS);

        $shipping = $expenseDocument->getOrCreateShipping($status);
        $this->assertEquals($shipping->getStatus(), $status);

        $status1 = $this->createMock(ShippingStatus::class);
        $status1->method('getId')->willReturn(ShippingStatus::PICKED_STATUS);

        $expenseDocument->updateShippingsStatus($status1);
        $this->assertEquals($shipping->getStatus(), $status1);
    }
}