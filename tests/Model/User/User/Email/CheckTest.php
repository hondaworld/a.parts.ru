<?php

namespace App\Tests\Model\User\User\Email;

use App\Model\User\Entity\User\Email;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class CheckTest extends TestCase
{
    public function testCheckActive(): void
    {
        $user = (new UserBuilder())->build();

        $anotherEmail = new Email('info@domen.ru', false, false);
        $another1Email = new Email('info1@domen.ru', false, false);

        $user->updateEmail(new Email('info@domen.ru', false, false), []);

        $this->assertTrue($user->getEmail()->isEqual($anotherEmail));
        $this->assertFalse($user->getEmail()->isEqual($another1Email));

        $this->assertEquals('info@domen.ru', $user->getEmail()->getValue());
        $this->assertFalse($user->getEmail()->isActive());
        $this->assertFalse($user->getEmail()->isActivated());
        $this->assertFalse($user->getEmail()->isNotification());
        $this->assertNull($user->getEmail()->getValueWithCheck());

        $user->updateEmail(new Email('info@domen.ru', true, false), []);

        $this->assertEquals('info@domen.ru', $user->getEmail()->getValue());
        $this->assertFalse($user->getEmail()->isActive());
        $this->assertTrue($user->getEmail()->isActivated());
        $this->assertTrue($user->getEmail()->isNotification());
        $this->assertEquals('info@domen.ru', $user->getEmail()->getValueWithCheck());

        $user->updateEmail(new Email('info@domen.ru', false, true), []);

        $this->assertEquals('info@domen.ru', $user->getEmail()->getValue());
        $this->assertTrue($user->getEmail()->isActive());
        $this->assertFalse($user->getEmail()->isActivated());
        $this->assertFalse($user->getEmail()->isNotification());
        $this->assertNull($user->getEmail()->getValueWithCheck());
    }
}