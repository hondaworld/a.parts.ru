<?php

namespace App\Tests\Model\Income\Income\ChangeStatus;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ReturningTest extends TestCase
{
    public function testReturning(): void
    {
        $manager = $this->createMock(Manager::class);
        $user = $this->createMock(User::class);
        $income = (new IncomeBuilder(2))->withIncomeSklad()->withOrderGood($manager, $user)->build();
        $incomeSklad = $income->getOneSkladOrCreate();
        $income->shipping($incomeSklad, $manager);
        $income->returning($incomeSklad);

        $this->assertEquals(0, $income->getQuantityPath());
        $this->assertEquals(0, $incomeSklad->getQuantityPath());
        $this->assertEquals(0, $income->getReserve());
        $this->assertEquals(0, $incomeSklad->getReserve());
        $this->assertCount(0, $income->getZapCardReserve());
    }
}