<?php

namespace App\Tests\Model\Card\Group;

use App\Model\Card\Entity\Category\ZapCategory;
use App\Model\Card\Entity\Group\ZapGroup;
use PHPUnit\Framework\TestCase;

class ZapGroupUpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $zapCategory = new ZapCategory('Название', 1);
        $zapCategory1 = new ZapCategory('Название новое', 2);
        $zapGroup = new ZapGroup('Группа', $zapCategory);

        $zapGroup->update('Группа новая', $zapCategory1);

        $this->assertEquals('Группа новая', $zapGroup->getName());
        $this->assertEquals($zapCategory1, $zapGroup->getZapCategory());
    }

    public function testUpdatePhoto(): void
    {
        $zapCategory = new ZapCategory('Название', 1);
        $zapGroup = new ZapGroup('Группа', $zapCategory);

        $this->assertEquals('', $zapGroup->getPhoto());

        $zapGroup->updatePhoto('image.jpg');

        $this->assertEquals('image.jpg', $zapGroup->getPhoto());
    }
}