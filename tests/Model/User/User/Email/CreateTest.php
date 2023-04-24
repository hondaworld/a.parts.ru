<?php

namespace App\Tests\Model\User\User\Email;

use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class CreateTest extends TestCase
{
    public function testCreate(): void
    {
        $user = (new UserBuilder())->build();

        $this->assertEquals('', $user->getEmail()->getValue());
        $this->assertFalse($user->getEmail()->isActive());
        $this->assertFalse($user->getEmail()->isActivated());
        $this->assertFalse($user->getEmail()->isNotification());
    }
}