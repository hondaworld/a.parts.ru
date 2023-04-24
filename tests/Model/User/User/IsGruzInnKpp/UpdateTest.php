<?php

namespace App\Tests\Model\User\User\IsGruzInnKpp;

use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $user = (new UserBuilder())->build();

        $this->assertFalse($user->isGruzInnKpp());

        $user->updateCashierSchetFak(true);
        $this->assertTrue($user->isGruzInnKpp());

        $user->updateCashierSchetFak(false);
        $this->assertFalse($user->isGruzInnKpp());
    }
}