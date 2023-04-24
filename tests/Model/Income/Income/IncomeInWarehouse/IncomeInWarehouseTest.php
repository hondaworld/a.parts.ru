<?php

namespace App\Tests\Model\Income\Income\IncomeInWarehouse;

use App\Model\Manager\Entity\Manager\Manager;
use App\Tests\Builder\Income\IncomeBuilder;
use App\Tests\Builder\Income\IncomeDocumentBuilder;
use PHPUnit\Framework\TestCase;

class IncomeInWarehouseTest extends TestCase
{
    public function testIncomeInWarehouse(): void
    {
        $manager = $this->createMock(Manager::class);
        $income = (new IncomeBuilder(10, 0, 10, 0, 0))->build();
        $incomeDocument = (new IncomeDocumentBuilder($manager))->build();
        $income->incomeInWarehouse($incomeDocument, $incomeDocument->getFirm());

        $this->assertEquals($incomeDocument->getFirm(), $income->getFirm());
        $this->assertEquals(10, $income->getQuantityIn());
        $this->assertEquals(0, $income->getQuantityPath());
        $this->assertEquals($incomeDocument, $income->getIncomeDocument());
        $this->assertNotNull($income->getDateofin());
    }
}