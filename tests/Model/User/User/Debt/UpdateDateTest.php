<?php

namespace App\Tests\Model\User\User\Debt;

use App\Model\User\Entity\User\Debt;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UpdateDateTest extends TestCase
{
    public function testUpdate(): void
    {
        $user = (new UserBuilder())->build();

        $d = new \DateTime('+3 days');
        $user->getDebt()->setDebtsDate($d);

        $this->assertEquals($d, $user->getDebt()->getDebtsDate());
    }
}