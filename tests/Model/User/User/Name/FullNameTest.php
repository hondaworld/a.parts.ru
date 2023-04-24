<?php

namespace App\Tests\Model\User\User\Name;

use App\Model\User\Entity\User\Name;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class FullNameTest extends TestCase
{
    public function testGetFullName(): void
    {
        $name = new Name(
            'Имя',
            'Фамилия',
            'Отчество'
        );
        $this->assertEquals('Имя Фамилия', $name->getFullname());
    }

    public function testCheckFirstNameEmpty(): void
    {
        $this->expectExceptionMessage('Expected a non-empty value. Got: ""');
        $name = new Name(
            '',
            null,
            null
        );
    }
}