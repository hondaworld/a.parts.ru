<?php

namespace App\Tests\Model\User\User;

use App\Model\User\Service\PhoneMobileHelper;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class PhoneMobileHelperTest extends TestCase
{
    public function testRussian(): void
    {
        $user = (new UserBuilder('+7 910 465 1911'))->build();
        $this->assertEquals('+7 (910) 465-19-11', (new PhoneMobileHelper($user->getPhonemob()))->getValue());
    }

    public function testUkraine(): void
    {
        $user = (new UserBuilder('+380 910 465 1911'))->build();
        $this->assertEquals('+380 (910) 465-19-11', (new PhoneMobileHelper($user->getPhonemob()))->getValue());
    }

    public function testBelarus(): void
    {
        $user = (new UserBuilder('+375 91 465 1911'))->build();
        $this->assertEquals('+375 (91) 465-19-11', (new PhoneMobileHelper($user->getPhonemob()))->getValue());
    }

    public function testUnknown(): void
    {
        $user = (new UserBuilder('+8 910 465 1911'))->build();
        $this->assertEquals($user->getPhonemob(), (new PhoneMobileHelper($user->getPhonemob()))->getValue());
    }
}