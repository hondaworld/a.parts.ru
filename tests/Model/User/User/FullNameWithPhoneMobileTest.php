<?php

namespace App\Tests\Model\User\User;

use App\Model\User\Service\PhoneMobileHelper;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class FullNameWithPhoneMobileTest extends TestCase
{
    public function testFullNameWithPhoneMobile(): void
    {
        $user = (new UserBuilder())->build();
        $this->assertEquals($user->getUserName()->getPassportname() . ', ' . (new PhoneMobileHelper($user->getPhonemob()))->getValue(), $user->getFullNameWithPhoneMobile());
    }
}