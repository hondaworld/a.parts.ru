<?php

namespace App\Tests\Model\User\User\Debt;

use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class BalanceTest extends TestCase
{
    public function testBalanceUpdate(): void
    {
        $user = (new UserBuilder())->build();
        $this->assertEquals(0, $user->getBalance());

        $user->changeBalance(-100);
        $this->assertEquals(-100, $user->getBalance());
        $this->assertNotNull($user->getDebt()->getDebtsDate());

        $user->changeBalance(100);
        $this->assertEquals(0, $user->getBalance());
        $this->assertNull($user->getDebt()->getDebtsDate());
    }
}