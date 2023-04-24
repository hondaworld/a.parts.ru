<?php

namespace App\Tests\Model\User\User\Ur;

use App\Model\User\Entity\User\Ur;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class PassportNameOrOrganizationTest extends TestCase
{
    public function testPassportNameOrOrganizationUrWithOrganization(): void
    {
        $user = (new UserBuilder())->withUr()->build();
        $this->assertEquals($user->getUr()->getOrganization(), $user->getPassportNameOrOrganization());
    }

    public function testPassportNameOrOrganizationUrWithoutOrganization(): void
    {
        $user = (new UserBuilder())->withUr(new Ur('', null, null, null, null, false, true))->build();
        $this->assertNotEquals($user->getUr()->getOrganization(), $user->getPassportNameOrOrganization());
    }

    public function testPassportNameOrOrganization(): void
    {
        $user = (new UserBuilder())->build();
        $this->assertEquals($user->getUserName()->getPassportname(), $user->getPassportNameOrOrganization());
    }

    public function testPassportNameOrOrganizationWithAddPersonName(): void
    {
        $user = (new UserBuilder())->build();
        $this->assertEquals('Частное лицо ' . $user->getUserName()->getPassportname(), $user->getPassportNameOrOrganization(true));
    }
}