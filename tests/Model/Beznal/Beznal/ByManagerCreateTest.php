<?php

namespace App\Tests\Model\Beznal\Beznal;

use App\Model\Beznal\Entity\Bank\Bank;
use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Manager\Entity\Manager\Manager;
use PHPUnit\Framework\TestCase;

class ByManagerCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $bank = new Bank('123456', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $manager = $this->createMock(Manager::class);
        $beznal = new Beznal($manager, $bank, '1234567890', 'Описание', true);

        $this->assertEquals($manager, $beznal->getManager());
        $this->assertNull($beznal->getFirm());
        $this->assertNull($beznal->getUser());
        $this->assertEquals($bank, $beznal->getBank());
        $this->assertEquals('1234567890', $beznal->getRasschet());
        $this->assertEquals('Описание', $beznal->getDescription());
        $this->assertTrue($beznal->isMain());
    }

    public function testCreateNull(): void
    {
        $bank = new Bank('123456', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $manager = $this->createMock(Manager::class);
        $beznal = new Beznal($manager, $bank, '1234567890', null, true);

        $this->assertEquals($manager, $beznal->getManager());
        $this->assertNull($beznal->getFirm());
        $this->assertNull($beznal->getUser());
        $this->assertEquals($bank, $beznal->getBank());
        $this->assertEquals('1234567890', $beznal->getRasschet());
        $this->assertEquals('', $beznal->getDescription());
        $this->assertTrue($beznal->isMain());
    }
}