<?php

namespace App\Tests\Model\Expense\Shipping;

use App\Model\Expense\Entity\ShippingPlace\ShippingPlace;
use App\Model\Expense\Entity\ShippingStatus\ShippingStatus;
use App\Tests\Builder\Expense\ExpenseDocumentBuilder;
use PHPUnit\Framework\TestCase;

class SippingPlaceTest extends TestCase
{
    public function testAddPlace(): void
    {
        $expenseDocument = (new ExpenseDocumentBuilder())->build();
        $shippingStatus = $this->createMock(ShippingStatus::class);
        $shipping = $expenseDocument->getOrCreateShipping($shippingStatus);

        $this->assertEquals($shippingStatus, $shipping->getStatus());

        $place = new ShippingPlace(1, 10, 15, 5, 22.5);
        $shipping->addPlace($place);

        $this->assertCount(1, $shipping->getPlaces());

        foreach ($shipping->getPlaces() as $item) {
            $this->assertEquals($item, $place);
        }
    }

    public function testCreate(): void
    {
        $place = new ShippingPlace(1, 10, 15, 5, 22.5);
        $this->assertEquals(1, $place->getNumber());
        $this->assertEquals(10, $place->getLength());
        $this->assertEquals(15, $place->getWidth());
        $this->assertEquals(5, $place->getHeight());
        $this->assertEquals(22.5, $place->getWeight());
    }

    public function testUpdate(): void
    {
        $place = new ShippingPlace(1, 10, 15, 5, 22.5);
        $place->update(2, 11, 16, 6, 33.6);
        $this->assertEquals(2, $place->getNumber());
        $this->assertEquals(11, $place->getLength());
        $this->assertEquals(16, $place->getWidth());
        $this->assertEquals(6, $place->getHeight());
        $this->assertEquals(33.6, $place->getWeight());
    }
}