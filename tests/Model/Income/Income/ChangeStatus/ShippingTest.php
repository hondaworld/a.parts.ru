<?php

namespace App\Tests\Model\Income\Income\ChangeStatus;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ShippingTest extends TestCase
{
    public function testShipping(): void
    {
        $manager = $this->createMock(Manager::class);
        $user = $this->createMock(User::class);
        $income = (new IncomeBuilder(2))->withIncomeSklad()->withOrderGood($manager, $user)->build();
        $incomeSklad = $income->getOneSkladOrCreate();
        $income->shipping($incomeSklad, $manager);

        $orderGood = $income->getFirstOrderGood();

        $this->assertEquals($income->getQuantity(), $income->getQuantityPath());
        $this->assertEquals($incomeSklad->getQuantity(), $incomeSklad->getQuantityPath());
        $this->assertEquals($income->getReserve(), $orderGood->getQuantity());
        $this->assertEquals($incomeSklad->getReserve(), $orderGood->getQuantity());
        $this->assertCount(1, $income->getZapCardReserve());
    }
}