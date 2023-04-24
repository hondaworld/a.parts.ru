<?php

namespace App\Tests\Model\User\User\Price;

use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class CreateTest extends TestCase
{
    public function testCreate(): void
    {
        $user = (new UserBuilder())->build();

        $this->assertEquals('', $user->getPrice()->getEmail());
        $this->assertEquals('', $user->getPrice()->getEmailSend());
        $this->assertEquals('', $user->getPrice()->getFilename());
        $this->assertFalse($user->getPrice()->isFirstLine());
        $this->assertEquals(0, $user->getPrice()->getLine());

        $this->assertEquals('', $user->getPrice()->getOrderNum());
        $this->assertEquals('', $user->getPrice()->getNumberNum());
        $this->assertEquals('', $user->getPrice()->getCreaterNum());
        $this->assertEquals('', $user->getPrice()->getQuantityNum());
        $this->assertEquals('', $user->getPrice()->getPriceNum());
    }
}