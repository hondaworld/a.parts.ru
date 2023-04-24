<?php

namespace App\Tests\Model\User\User\Debt;

use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class CreateTest extends TestCase
{
    public function testCreate(): void
    {
        $user = (new UserBuilder())->build();

        $this->assertNull($user->getDebt()->getDebtsDate());
        $this->assertEquals(3, $user->getDebt()->getDebtsDays());
        $this->assertEquals(0, $user->getDebt()->getDebtInDays());
    }
}