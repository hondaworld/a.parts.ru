<?php

namespace App\Tests\Model\Firm\Firm;

use App\Model\Finance\Entity\Nalog\Nalog;
use App\Model\Firm\Entity\Firm\Firm;
use App\Tests\Builder\Manager\ManagerBuilder;
use PHPUnit\Framework\TestCase;

class FirmUpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $nalog = new Nalog('Налог');
        $nalog->addNds(new \DateTime('2019-01-01'), 20);
        $firm = new Firm('Короткое название', 'ООО "Тестовая компания"', null, null, null, null, true, true, $nalog, null, null);

        $director = (new ManagerBuilder('director'))->build();
        $buhgalter = (new ManagerBuilder('buhgalter'))->build();

        $d1 = new \DateTime('-2 days');
        $d2 = new \DateTime('-1 day');

        $firm->update('Короткое название новое', 'ООО "Тестовая компания новая"', '12312323', '77000012', '7711211111', '77151233333', false, false, $nalog, $director, $buhgalter, $d1, $d2);

        $this->assertEquals('Короткое название новое', $firm->getNameShort());
        $this->assertEquals('ООО "Тестовая компания новая"', $firm->getName());
        $this->assertEquals($nalog, $firm->getNalog());
        $this->assertEquals('12312323', $firm->getInn());
        $this->assertEquals('77000012', $firm->getKpp());
        $this->assertEquals('7711211111', $firm->getOkpo());
        $this->assertEquals('77151233333', $firm->getOgrn());
        $this->assertEquals($director, $firm->getDirector());
        $this->assertEquals($buhgalter, $firm->getBuhgalter());
        $this->assertFalse($firm->isNDS());
        $this->assertFalse($firm->isUr());
        $this->assertEquals($d1, $firm->getDateofadded());
        $this->assertEquals($d2, $firm->getDateofclosed());
    }

    public function testUpdateOthers(): void
    {
        $nalog = new Nalog('Налог');
        $nalog->addNds(new \DateTime('2019-01-01'), 20);

        $director = (new ManagerBuilder('director'))->build();
        $buhgalter = (new ManagerBuilder('buhgalter'))->build();

        $firm = new Firm('Короткое название', 'ООО "Тестовая компания"', '12312323', '77000012', '7711211111', '77151233333', false, false, $nalog, $director, $buhgalter);

        $this->assertEquals(1, $firm->getFirstSchet());
        $this->assertEquals(1, $firm->getFirstNakladnaya());
        $this->assertEquals(1, $firm->getFirstSchetfak());

        $firm->updateOthers(3, 6, 2);

        $this->assertEquals(3, $firm->getFirstSchet());
        $this->assertEquals(6, $firm->getFirstNakladnaya());
        $this->assertEquals(2, $firm->getFirstSchetfak());
    }
}