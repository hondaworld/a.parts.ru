<?php

namespace App\Tests\Model\User\User\EmailPrice;

use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class CreateTest extends TestCase
{
    public function testCreate(): void
    {
        $user = (new UserBuilder())->build();

        $this->assertEquals('', $user->getEmailPrice()->getValue());
        $this->assertEquals(0, $user->getEmailPrice()->getZapSkladID());
        $this->assertFalse($user->getEmailPrice()->isPrice());
        $this->assertFalse($user->getEmailPrice()->isPriceSummary());
    }
}