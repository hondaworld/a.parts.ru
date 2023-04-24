<?php

namespace App\Tests\Model\User\User\Ur;

use App\Model\User\Entity\User\Ur;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class OrganizationWithInnAndKppTest extends TestCase
{
    public function testWithInnAndKpp(): void
    {
        $user = (new UserBuilder())->build();
        $ur = new Ur(
            'ООО "Запчасти"',
            '1234567',
            '77000011',
            null,
            null,
            null,
            true,
            null,
            null
        );
        $user->updateUr('', $ur);

        $this->assertEquals('ООО "Запчасти", ИНН/КПП 1234567/77000011', $user->getUr()->getOrganizationWithInnAndKpp());
    }

    public function testWithInnWithoutKpp(): void
    {
        $user = (new UserBuilder())->build();
        $ur = new Ur(
            'ООО "Запчасти"',
            '1234567',
            null,
            null,
            null,
            null,
            true,
            null,
            null
        );
        $user->updateUr('', $ur);

        $this->assertEquals('ООО "Запчасти", ИНН 1234567', $user->getUr()->getOrganizationWithInnAndKpp());
    }
    public function testWithoutInnWithKpp(): void
    {
        $user = (new UserBuilder())->build();
        $ur = new Ur(
            'ООО "Запчасти"',
            null,
            '77000011',
            null,
            null,
            null,
            true,
            null,
            null
        );
        $user->updateUr('', $ur);

        $this->assertEquals('ООО "Запчасти", КПП 77000011', $user->getUr()->getOrganizationWithInnAndKpp());
    }

    public function testWithoutInnAndKpp(): void
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

        $this->assertEquals('ООО "Запчасти"', $user->getUr()->getOrganizationWithInnAndKpp());
    }
}