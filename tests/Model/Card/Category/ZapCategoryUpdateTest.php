<?php

namespace App\Tests\Model\Card\Category;

use App\Model\Card\Entity\Category\ZapCategory;
use PHPUnit\Framework\TestCase;

class ZapCategoryUpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $zapCategory = new ZapCategory('Название', 1);

        $zapCategory->update('Название новое');

        $this->assertEquals('Название новое', $zapCategory->getName());
        $this->assertEquals(1, $zapCategory->getNumber());
    }
}