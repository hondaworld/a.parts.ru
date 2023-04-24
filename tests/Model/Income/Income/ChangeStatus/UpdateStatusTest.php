<?php

namespace App\Tests\Model\Income\Income\ChangeStatus;

use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class UpdateStatusTest extends TestCase
{
    public function testStatus(): void
    {
        $income = (new IncomeBuilder(2))->build();
        $incomeStatus = new IncomeStatus(IncomeStatus::ORDERED);
        $income->updateStatus($incomeStatus);

        $this->assertEquals($incomeStatus, $income->getStatus());
        $this->assertCount(0, $income->getIncomeStatusHistory());
    }

    public function testStatusWithManager(): void
    {
        $manager = $this->createMock(Manager::class);
        $income = (new IncomeBuilder(2))->build();
        $incomeStatus = new IncomeStatus(IncomeStatus::ORDERED);
        $income->updateStatus($incomeStatus, $manager);

        $this->assertEquals($incomeStatus, $income->getStatus());
        $this->assertCount(1, $income->getIncomeStatusHistory());
    }

    public function testStatusWithOrderGood(): void
    {
        $manager = $this->createMock(Manager::class);
        $user = $this->createMock(User::class);
        $income = (new IncomeBuilder(2))->withIncomeSklad()->withOrderGood($manager, $user)->build();
        $incomeStatus = new IncomeStatus(IncomeStatus::ORDERED);
        $income->updateStatus($incomeStatus, $manager);

        $this->assertEquals($incomeStatus, $income->getStatus());
        $this->assertCount(1, $income->getIncomeStatusHistory());
        $this->assertEquals($incomeStatus, $income->getFirstOrderGood()->getLastIncomeStatus());
    }
}