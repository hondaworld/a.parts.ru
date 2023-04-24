<?php

namespace App\Tests\Model\Order\Dates;

use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class DateOfServiceTest extends TestCase
{
    public function testUpdate(): void
    {
        $user = (new UserBuilder())->build();
        $this->assertNull($user->getDateofservice());

        $d = new \DateTime('-1 day');
        $user->updateDateOfService($d);
        $this->assertEquals($d, $user->getDateofservice());
    }
}