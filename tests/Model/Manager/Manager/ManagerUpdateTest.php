<?php

namespace App\Tests\Model\Manager\Manager;

use App\Model\Manager\Entity\Manager\Email;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Manager\Entity\Manager\Name;
use App\Model\Manager\Entity\Type\ManagerType;
use PHPUnit\Framework\TestCase;

class ManagerUpdateTest extends TestCase
{
    public function testCreate(): void
    {
        $type = new ManagerType();
        $name = new Name(
            'Имя',
            'Фамилия',
            'Отчество'
        );
        $name1 = new Name(
            'Имя новое',
            'Фамилия новая',
            'Отчество новое'
        );

        $manager = new Manager(
            'login',
            '111',
            $name,
            $name->generateName(),
            $type
        );

        $d = new \DateTime('2003-04-01');
        $email = new Email('info@hondaworld.ru');

        $manager->update('login1', '+791111', $name1, 'Фамилия новая А. В.', 'ФМм', $email, 'M', false, true, false, $d, 'sss.jpg', $type, '23', '12');

        $this->assertEquals('login1', $manager->getLogin());
        $this->assertEquals('+791111', $manager->getPhonemob());
        $this->assertEquals('Фамилия новая А. В.', $manager->getName());
        $this->assertEquals($type, $manager->getType());
        $this->assertEquals('ФММ', $manager->getNick());
        $this->assertEquals($email, $manager->getEmail());
        $this->assertEquals('M', $manager->getSex());
        $this->assertFalse($manager->getIsHide());
        $this->assertTrue($manager->getIsManager());
        $this->assertFalse($manager->getIsHide());
        $this->assertEquals('2003-04-01', $manager->getDateofmanger()->format('Y-m-d'));
        $this->assertEquals('sss.jpg', $manager->getPhoto());
        $this->assertEquals('23', $manager->getZpSpare());
        $this->assertEquals('12', $manager->getZpService());
    }
}