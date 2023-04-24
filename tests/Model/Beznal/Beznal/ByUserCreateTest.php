<?php

namespace App\Tests\Model\Beznal\Beznal;

use App\Model\Beznal\Entity\Bank\Bank;
use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\User\Entity\User\User;
use PHPUnit\Framework\TestCase;

class ByUserCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $bank = new Bank('123456', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $user = $this->createMock(User::class);
        $beznal = new Beznal($user, $bank, '1234567890', 'Описание', true);

        $this->assertEquals($user, $beznal->getUser());
        $this->assertNull($beznal->getFirm());
        $this->assertNull($beznal->getManager());
        $this->assertEquals($bank, $beznal->getBank());
        $this->assertEquals('1234567890', $beznal->getRasschet());
        $this->assertEquals('Описание', $beznal->getDescription());
        $this->assertTrue($beznal->isMain());
    }

    public function testCreateNull(): void
    {
        $bank = new Bank('123456', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $user = $this->createMock(User::class);
        $beznal = new Beznal($user, $bank, '1234567890', null, true);

        $this->assertEquals($user, $beznal->getUser());
        $this->assertNull($beznal->getFirm());
        $this->assertNull($beznal->getManager());
        $this->assertEquals($bank, $beznal->getBank());
        $this->assertEquals('1234567890', $beznal->getRasschet());
        $this->assertEquals('', $beznal->getDescription());
        $this->assertTrue($beznal->isMain());
    }
}