<?php

namespace App\Tests\Model\Order\Dates;

use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class DateOfDeliveryTest extends TestCase
{
    public function testUpdate(): void
    {
        $user = (new UserBuilder())->build();
        $this->assertNull($user->getDateofdelivery());

        $d = new \DateTime('-1 day');
        $user->updateDateOfDelivery($d);
        $this->assertEquals($d, $user->getDateofdelivery());
    }
}