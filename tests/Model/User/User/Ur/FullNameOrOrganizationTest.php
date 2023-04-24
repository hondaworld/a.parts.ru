<?php

namespace App\Tests\Model\User\User\Ur;

use App\Model\User\Entity\User\Ur;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class FullNameOrOrganizationTest extends TestCase
{
    public function testFullNameOrOrganizationUrWithOrganization(): void
    {
        $user = (new UserBuilder())->withUr()->build();
        $this->assertEquals($user->getUr()->getOrganization(), $user->getFullNameOrOrganization());
    }

    public function testFullNameOrOrganizationUrWithoutOrganization(): void
    {
        $user = (new UserBuilder())->withUr(new Ur('', null, null, null, null, false, true))->build();
        $this->assertNotEquals($user->getUr()->getOrganization(), $user->getFullNameOrOrganization());
    }

    public function testFullNameOrOrganization(): void
    {
        $user = (new UserBuilder())->build();
        $this->assertEquals($user->getUserName()->getFullname(), $user->getFullNameOrOrganization());
    }

    public function testFullNameOrOrganizationWithAddPersonName(): void
    {
        $user = (new UserBuilder())->build();
        $this->assertEquals('Частное лицо ' . $user->getUserName()->getFullname(), $user->getFullNameOrOrganization(true));
    }
}