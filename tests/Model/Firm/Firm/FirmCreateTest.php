<?php

namespace App\Tests\Model\Firm\Firm;

use App\Model\Finance\Entity\Nalog\Nalog;
use App\Model\Firm\Entity\Firm\Firm;
use App\Tests\Builder\Manager\ManagerBuilder;
use PHPUnit\Framework\TestCase;

class FirmCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $nalog = new Nalog('Налог');
        $nalog->addNds(new \DateTime('2019-01-01'), 20);
        $firm = new Firm('Короткое название', 'ООО "Тестовая компания"', null, null, null, null, true, true, $nalog, null, null);

        $this->assertEquals('Короткое название', $firm->getNameShort());
        $this->assertEquals('ООО "Тестовая компания"', $firm->getName());
        $this->assertEquals($nalog, $firm->getNalog());
        $this->assertEquals('', $firm->getInn());
        $this->assertEquals('', $firm->getKpp());
        $this->assertEquals('', $firm->getOkpo());
        $this->assertEquals('', $firm->getOgrn());
        $this->assertNull($firm->getDirector());
        $this->assertNull($firm->getBuhgalter());
        $this->assertTrue($firm->isNDS());
        $this->assertTrue($firm->isUr());
    }

    public function testCreateFull(): void
    {
        $nalog = new Nalog('Налог');
        $nalog->addNds(new \DateTime('2019-01-01'), 20);

        $director = (new ManagerBuilder('director'))->build();
        $buhgalter = (new ManagerBuilder('buhgalter'))->build();

        $firm = new Firm('Короткое название', 'ООО "Тестовая компания"', '12312323', '77000012', '7711211111', '77151233333', false, false, $nalog, $director, $buhgalter);

        $this->assertEquals('Короткое название', $firm->getNameShort());
        $this->assertEquals('ООО "Тестовая компания"', $firm->getName());
        $this->assertEquals($nalog, $firm->getNalog());
        $this->assertEquals('12312323', $firm->getInn());
        $this->assertEquals('77000012', $firm->getKpp());
        $this->assertEquals('7711211111', $firm->getOkpo());
        $this->assertEquals('77151233333', $firm->getOgrn());
        $this->assertEquals($director, $firm->getDirector());
        $this->assertEquals($buhgalter, $firm->getBuhgalter());
        $this->assertFalse($firm->isNDS());
        $this->assertFalse($firm->isUr());
    }
}