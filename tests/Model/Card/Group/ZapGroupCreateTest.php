<?php

namespace App\Tests\Model\Card\Group;

use App\Model\Card\Entity\Category\ZapCategory;
use App\Model\Card\Entity\Group\ZapGroup;
use PHPUnit\Framework\TestCase;

class ZapGroupCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $zapCategory = new ZapCategory('Название', 1);
        $zapGroup = new ZapGroup('Группа', $zapCategory);

        $this->assertEquals('Группа', $zapGroup->getName());
        $this->assertEquals($zapCategory, $zapGroup->getZapCategory());
    }
}