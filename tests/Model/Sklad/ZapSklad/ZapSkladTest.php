<?php

namespace App\Tests\Model\Sklad\ZapSklad;

use App\Model\Sklad\Entity\PriceList\PriceList;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\User\Entity\Opt\Opt;
use PHPUnit\Framework\TestCase;

class ZapSkladTest extends TestCase
{
    public function testCreate(): void
    {
        $zapSklad = new ZapSklad('Кратно', 'Название', true, 1, null, false);
        $this->assertEquals('Кратно', $zapSklad->getNameShort());
        $this->assertEquals('Название', $zapSklad->getName());
        $this->assertTrue($zapSklad->isTorg());
        $this->assertEquals(1, $zapSklad->getKoef());
        $this->assertNull($zapSklad->getOpt());
        $this->assertFalse($zapSklad->isMain());
    }

    public function testCreateOpt(): void
    {
        $opt = new Opt('Розница', 1);
        $zapSklad = new ZapSklad('Кратно', 'Название', true, 1, $opt, false);
        $this->assertEquals('Кратно', $zapSklad->getNameShort());
        $this->assertEquals('Название', $zapSklad->getName());
        $this->assertTrue($zapSklad->isTorg());
        $this->assertEquals(1, $zapSklad->getKoef());
        $this->assertEquals($opt, $zapSklad->getOpt());
        $this->assertFalse($zapSklad->isMain());
    }

    public function testUdate(): void
    {
        $opt = new Opt('Розница', 1);
        $zapSklad = new ZapSklad('Кратно', 'Название', true, 1, null, false);
        $zapSklad->update('Кратно новое', 'Название новое', false, 1.2, $opt, true);
        $this->assertEquals('Кратно новое', $zapSklad->getNameShort());
        $this->assertEquals('Название новое', $zapSklad->getName());
        $this->assertFalse($zapSklad->isTorg());
        $this->assertEquals(1.2, $zapSklad->getKoef());
        $this->assertEquals($opt, $zapSklad->getOpt());
        $this->assertTrue($zapSklad->isMain());
    }
}