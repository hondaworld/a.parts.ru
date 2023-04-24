<?php

namespace App\Tests\Model\User\User\Debt;

use App\Model\User\Entity\User\Debt;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class IsDebtTest extends TestCase
{
    public function testIsDebt(): void
    {
        $user = (new UserBuilder())->build();

        $this->assertFalse($user->isDebt());

        $user->updateDebt(0, new Debt(3, 2));

        $d = new \DateTime('-3 days');
        $user->getDebt()->setDebtsDate($d);
        $this->assertFalse($user->isDebt());

        $d = new \DateTime('-4 days');
        $user->getDebt()->setDebtsDate($d);
        $this->assertTrue($user->isDebt());
    }
}