<?php

namespace App\Tests\Model\User\User\Ur;

use App\Model\User\Entity\User\Ur;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class PassportNameOrOrganizationWithInnKppAndPhonemobTest extends TestCase
{
    public function testPassportNameOrOrganizationWithInnKppAndPhonemobUrWithOrganization(): void
    {
        $user = (new UserBuilder())->withUr()->build();
        $this->assertEquals($user->getUr()->getOrganizationWithInnAndKpp(), $user->getPassportNameOrOrganizationWithInnKppAndPhonemob());
    }

    public function testPassportNameOrOrganizationWithInnKppAndPhonemobUrWithoutOrganization(): void
    {
        $user = (new UserBuilder())->withUr(new Ur('', null, null, null, null, false, true))->build();
        $this->assertNotEquals($user->getUr()->getOrganizationWithInnAndKpp(), $user->getPassportNameOrOrganizationWithInnKppAndPhonemob());
    }

    public function testPassportNameOrOrganizationWithInnKppAndPhonemob(): void
    {
        $user = (new UserBuilder())->build();
        $this->assertEquals($user->getFullNameWithPhoneMobile(), $user->getPassportNameOrOrganizationWithInnKppAndPhonemob());
    }

    public function testPassportNameOrOrganizationWithInnKppAndPhonemobWithAddPersonName(): void
    {
        $user = (new UserBuilder())->build();
        $this->assertEquals('Частное лицо ' . $user->getFullNameWithPhoneMobile(), $user->getPassportNameOrOrganizationWithInnKppAndPhonemob(true));
    }
}