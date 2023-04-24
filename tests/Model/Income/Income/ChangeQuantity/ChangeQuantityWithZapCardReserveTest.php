<?php

namespace App\Tests\Model\Income\Income\ChangeQuantity;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ChangeQuantityWithZapCardReserveTest extends TestCase
{
    public function testQuantityMore(): void
    {
        $manager = $this->createMock(Manager::class);
        $user = $this->createMock(User::class);
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->withIncomeSklad()->withOrderGood($manager, $user, true)->build();

        $this->assertEquals(10, $income->getZapCardReserve()[0]->getQuantity());
        $income->changeQuantity(15);
        $this->assertEquals(15, $income->getZapCardReserve()[0]->getQuantity());
    }

    public function testQuantityLess(): void
    {
        $manager = $this->createMock(Manager::class);
        $user = $this->createMock(User::class);
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->withIncomeSklad()->withOrderGood($manager, $user, true)->build();

        $this->assertEquals(10, $income->getZapCardReserve()[0]->getQuantity());
        $income->changeQuantity(5);
        $this->assertEquals(5, $income->getZapCardReserve()[0]->getQuantity());
    }
}