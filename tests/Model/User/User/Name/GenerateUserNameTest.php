<?php

namespace App\Tests\Model\User\User\Name;

use App\Model\User\Entity\User\Name;
use PHPUnit\Framework\TestCase;

class GenerateUserNameTest extends TestCase
{
    public function testGenerateFullName(): void
    {
        $name = new Name(
            'имя',
            'фамилия',
            'отчество'
        );
        $this->assertEquals('Фамилия И. О.', $name->generateName());
        $this->assertEquals('фамилия имя отчество', $name->getPassportname());
    }

    public function testGenerateFullNameWithoutMiddleName(): void
    {
        $name = new Name(
            'имя',
            'фамилия',
            null
        );
        $this->assertEquals('Фамилия И.', $name->generateName());
        $this->assertEquals('фамилия имя', $name->getPassportname());
    }

    public function testGenerateFullNameWithoutMiddleNameAndLastName(): void
    {
        $name = new Name(
            'имя',
            null,
            null
        );
        $this->assertEquals('Имя', $name->generateName());
        $this->assertEquals(' имя', $name->getPassportname());
    }
}