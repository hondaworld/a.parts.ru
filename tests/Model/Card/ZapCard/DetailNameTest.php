<?php

namespace App\Tests\Model\Card\ZapCard;

use App\Model\Card\Entity\Category\ZapCategory;
use App\Model\Card\Entity\Group\ZapGroup;
use App\Tests\Builder\Card\ZapCardBuilder;
use PHPUnit\Framework\TestCase;

class DetailNameTest extends TestCase
{
    public function testNames(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapCard->updateName(null, 'Название', 'Описание', 'Название большое', 'English name');
        $this->assertEquals('Название', $zapCard->getName());
        $this->assertEquals('Описание', $zapCard->getDescription());
        $this->assertEquals('Название большое', $zapCard->getNameBig());
        $this->assertEquals('English name', $zapCard->getNameEng());
    }

    public function testDetailNameWithoutGroup(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapCard->updateName(null, 'Название', 'Описание', 'Название большое', 'English name');
        $this->assertEquals('Название большое', $zapCard->getDetailName());
    }

    public function testDetailNameWithGroupWithoutName(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapGroup = new ZapGroup('Тестовая группа', new ZapCategory('Тестовая категория', 1));
        $zapCard->updateName($zapGroup, '', 'Описание', 'Название большое', 'English name');
        $this->assertEquals('Тестовая группа Описание', $zapCard->getDetailName());
    }

    public function testDetailNameWithGroupWithoutDescription(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapGroup = new ZapGroup('Тестовая группа', new ZapCategory('Тестовая категория', 1));
        $zapCard->updateName($zapGroup, 'Название', '', 'Название большое', 'English name');
        $this->assertEquals('Тестовая группа Название', $zapCard->getDetailName());
    }

    public function testDetailNameWithGroup(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapGroup = new ZapGroup('Тестовая группа', new ZapCategory('Тестовая категория', 1));
        $zapCard->updateName($zapGroup, 'Название', 'Описание', 'Название большое', 'English name');
        $this->assertEquals('Тестовая группа Название Описание', $zapCard->getDetailName());
    }
}