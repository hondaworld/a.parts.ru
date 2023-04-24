<?php

namespace App\Tests\Model\Contact\Town;

use App\Model\Contact\Entity\TownType\TownType;
use PHPUnit\Framework\TestCase;

class TownTypeCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $type = new TownType('г.', 'город');

        $this->assertEquals('г.', $type->getNameShort());
        $this->assertEquals('город', $type->getName());
    }
}