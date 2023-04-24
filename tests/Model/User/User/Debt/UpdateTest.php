<?php

namespace App\Tests\Model\User\User\Debt;

use App\Model\User\Entity\User\Debt;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $user = (new UserBuilder())->build();

        $this->assertNull($user->getDebt()->getDebtsDate());

        $user->updateDebt(0, new Debt(null, null));

        $this->assertEquals(0, $user->getDebt()->getDebtsDays());
        $this->assertEquals(0, $user->getDebt()->getDebtInDays());
        $this->assertNull($user->getDebt()->getDebtsDate());

        $user->updateDebt(0, new Debt(3, 2));

        $this->assertEquals(3, $user->getDebt()->getDebtsDays());
        $this->assertEquals(2, $user->getDebt()->getDebtInDays());
        $this->assertNull($user->getDebt()->getDebtsDate());
    }
}