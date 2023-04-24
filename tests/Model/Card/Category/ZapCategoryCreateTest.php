<?php

namespace App\Tests\Model\Card\Category;

use App\Model\Card\Entity\Category\ZapCategory;
use PHPUnit\Framework\TestCase;

class ZapCategoryCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $zapCategory = new ZapCategory('Название', 1);

        $this->assertEquals('Название', $zapCategory->getName());
        $this->assertEquals(1, $zapCategory->getNumber());
    }
}