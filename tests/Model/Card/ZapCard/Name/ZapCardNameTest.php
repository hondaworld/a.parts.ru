<?php

namespace App\Tests\Model\Card\ZapCard\Name;

use App\Model\Card\Entity\Category\ZapCategory;
use App\Model\Card\Entity\Group\ZapGroup;
use App\Tests\Builder\Card\ZapCardBuilder;
use PHPUnit\Framework\TestCase;

class ZapCardNameTest extends TestCase
{
    public function testName(): void
    {
        $zapCard = (new ZapCardBuilder())->build();

        $zapGroup = new ZapGroup('Группа запчастей', new ZapCategory('Категория запчастей', 1));

        $zapCard->updateName($zapGroup, 'Название', 'Описание', 'Название большое', 'English name');
        $this->assertEquals($zapGroup, $zapCard->getZapGroup());
        $this->assertEquals('Название', $zapCard->getName());
        $this->assertEquals('Описание', $zapCard->getDescription());
        $this->assertEquals('Название большое', $zapCard->getNameBig());
        $this->assertEquals('English name', $zapCard->getNameEng());

        $zapCard->updateName(null, null, null, null, null);
        $this->assertNull($zapCard->getZapGroup());
        $this->assertEquals('', $zapCard->getName());
        $this->assertEquals('', $zapCard->getDescription());
        $this->assertEquals('', $zapCard->getNameBig());
        $this->assertEquals('', $zapCard->getNameEng());
    }
}