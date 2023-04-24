<?php

namespace App\Tests\Model\User\User\Ur;

use App\Model\User\Service\PhoneMobileHelper;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class FullNameWithPhoneMobileAndOrganizationTest extends TestCase
{
    public function testFullNameWithPhoneMobileAndOrganizationWithoutOrganization(): void
    {
        $user = (new UserBuilder())->build();
        $this->assertEquals($user->getUserName()->getPassportname() . ' - ' . (new PhoneMobileHelper($user->getPhonemob()))->getValue(), $user->getFullNameWithPhoneMobileAndOrganization());
    }

    public function testFullNameWithPhoneMobileAndOrganizationWithOrganization(): void
    {
        $user = (new UserBuilder())->withUr()->build();
        $this->assertEquals($user->getUserName()->getPassportname() . ' - ' . (new PhoneMobileHelper($user->getPhonemob()))->getValue() . ' (' . $user->getUr()->getOrganization() . ')', $user->getFullNameWithPhoneMobileAndOrganization());
    }
}