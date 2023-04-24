<?php

namespace App\Tests\Model\Manager\Manager;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Manager\Entity\Manager\Name;
use App\Model\Manager\Entity\Type\ManagerType;
use PHPUnit\Framework\TestCase;

class ManagerCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $type = new ManagerType();
        $name = new Name(
            'Имя',
            'Фамилия',
            'Отчество'
        );

        $manager = new Manager(
            'login',
            '111',
            $name,
            $name->generateName(),
            $type
        );

        $this->assertEquals('login', $manager->getLogin());
        $this->assertEquals('Фамилия И. О.', $manager->getName());
        $this->assertEquals($type, $manager->getType());
        $this->assertEquals('ФАМ', $manager->getNick());
    }
}