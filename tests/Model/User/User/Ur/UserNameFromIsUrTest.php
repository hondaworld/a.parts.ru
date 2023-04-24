<?php

namespace App\Tests\Model\User\User\Ur;

use App\Model\User\Entity\User\Ur;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UserNameFromIsUrTest extends TestCase
{
    public function testUserNameIsNotUr(): void
    {
        $user = (new UserBuilder())->build();
        $ur = new Ur(
            'ООО "Запчасти"',
            null,
            null,
            null,
            null,
            null,
            false,
            null,
            null
        );
        $user->updateUr('', $ur);

        $this->assertEquals($user->generateName(), $user->getUserName()->generateName());
    }

    public function testUserNameIsUr(): void
    {
        $user = (new UserBuilder())->build();
        $ur = new Ur(
            'ООО "Запчасти"',
            null,
            null,
            null,
            null,
            null,
            true,
            null,
            null
        );
        $user->updateUr('', $ur);

        $this->assertEquals($user->generateName(), $user->getUr()->getOrganization());
    }

    public function testUserNameIsUrAndOrganizationEmpty(): void
    {
        $user = (new UserBuilder())->build();
        $ur = new Ur(
            null,
            null,
            null,
            null,
            null,
            null,
            true,
            null,
            null
        );
        $user->updateUr('', $ur);

        $this->assertEquals($user->generateName(), $user->getUserName()->generateName());
    }
}