<?php

namespace App\Tests\Model\Beznal\Beznal;

use App\Model\Beznal\Entity\Bank\Bank;
use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\User\Entity\User\User;
use PHPUnit\Framework\TestCase;

class ByUserUpdateTest extends TestCase
{
    public function testCreate(): void
    {
        $bank = new Bank('123456', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $user = $this->createMock(User::class);
        $beznal = new Beznal($user, $bank, '1234567890', 'Описание', true);

        $bank1 = new Bank('1234567', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $beznal->update($bank1, '987654321', 'Описание новое', false);

        $this->assertEquals($bank1, $beznal->getBank());
        $this->assertEquals('987654321', $beznal->getRasschet());
        $this->assertEquals('Описание новое', $beznal->getDescription());
        $this->assertFalse($beznal->isMain());
    }

    public function testCreateNull(): void
    {
        $bank = new Bank('123456', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $user = $this->createMock(User::class);
        $beznal = new Beznal($user, $bank, '1234567890', 'Описание', true);

        $bank1 = new Bank('1234567', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $beznal->update($bank1, '987654321', null, false);

        $this->assertEquals($bank1, $beznal->getBank());
        $this->assertEquals('987654321', $beznal->getRasschet());
        $this->assertEquals('', $beznal->getDescription());
        $this->assertFalse($beznal->isMain());
    }
}