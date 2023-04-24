<?php

namespace App\Tests\Model\Manager\Manager\Name;

use App\Model\Manager\Entity\Manager\Name;
use PHPUnit\Framework\TestCase;

class GenerateManagerNameTest extends TestCase
{
    public function testGenerateFullName(): void
    {
        $name = new Name(
            'имя',
            'фамилия',
            'отчество'
        );
        $this->assertEquals('Фамилия И. О.', $name->generateName());
        $this->assertEquals('ФАМ', $name->generateNick());
    }

    public function testGenerateFullNameWithoutMiddleName(): void
    {
        $name = new Name(
            'имя',
            'фамилия',
            ''
        );
        $this->assertEquals('Фамилия И.', $name->generateName());
        $this->assertEquals('ФАМ', $name->generateNick());
    }

    public function testGenerateFullNameWithoutMiddleNameAndLastName(): void
    {
        $name = new Name(
            'имя',
            '',
            ''
        );
        $this->assertEquals(' И.', $name->generateName());
        $this->assertEquals('ИМЯ', $name->generateNick());
    }
}