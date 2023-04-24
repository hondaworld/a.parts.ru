<?php

namespace App\Tests\Model\User\User;

use App\Model\User\Entity\Opt\OptRepository;
use App\Model\User\Entity\User\Name;
use App\Model\User\Entity\User\User;
use App\Model\User\UseCase\User\Phonemob;
use PHPUnit\Framework\TestCase;

class UserCreateTest extends TestCase
{
    public function testUserCreate(): void
    {
        $optRepository = $this->createMock(OptRepository::class);
        $opt = $optRepository->get(1);

//        $opt = new Opt('Розница', 1);

        $name = new Name(
            'Имя',
            'Фамилия',
            'Отчество'
        );

        $user = new User(
            $opt,
            (new Phonemob('+7 910 465 1911'))->getValue(),
            $name,
            $name->generateName(),
            null,
        );

        $this->assertEquals('+79104651911', $user->getPhonemob());
        $this->assertEquals('Фамилия И. О.', $user->getName());

//        self::assertEquals('+79104651911', $user->getPhonemob());
//        self::assertEquals('Фамилия И. О.', $user->getName());
    }
}