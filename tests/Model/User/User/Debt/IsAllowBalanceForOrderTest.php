<?php

namespace App\Tests\Model\User\User\Debt;

use App\Model\User\Entity\User\Debt;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class IsAllowBalanceForOrderTest extends TestCase
{
    public function testIsAllowBalanceForOrder(): void
    {
        $user = (new UserBuilder())->build();

        $user->updateDebt(0, new Debt(0, 0));

        $this->assertEquals(0, $user->getBalance());
        $this->assertEquals(0, $user->getBalanceLimit());
        $this->assertTrue($user->isAllowBalanceForOrder(0));
        $this->assertTrue($user->isAllowBalanceForOrder(-1));
        $this->assertFalse($user->isAllowBalanceForOrder(1));

        $user->updateBalanceLimit(10);

        $this->assertTrue($user->isAllowBalanceForOrder(0));
        $this->assertTrue($user->isAllowBalanceForOrder(-1));
        $this->assertFalse($user->isAllowBalanceForOrder(1));

        $user->updateDebt(10, new Debt(1, 0));

        $this->assertTrue($user->isAllowBalanceForOrder(0));
        $this->assertTrue($user->isAllowBalanceForOrder(-1));
        $this->assertTrue($user->isAllowBalanceForOrder(1));

        $this->assertFalse($user->isAllowBalanceForOrder(11));
    }
}