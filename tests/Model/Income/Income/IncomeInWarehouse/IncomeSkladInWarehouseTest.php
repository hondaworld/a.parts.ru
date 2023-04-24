<?php

namespace App\Tests\Model\Income\Income\IncomeInWarehouse;

use App\Model\Manager\Entity\Manager\Manager;
use App\Tests\Builder\Income\IncomeBuilder;
use App\Tests\Builder\Income\IncomeDocumentBuilder;
use PHPUnit\Framework\TestCase;

class IncomeSkladInWarehouseTest extends TestCase
{
    public function testIncomeInWarehouse(): void
    {
        $manager = $this->createMock(Manager::class);
        $income = (new IncomeBuilder(10, 0, 10, 0, 0))->build();
        $incomeDocument = (new IncomeDocumentBuilder($manager))->build();
        $incomeSklad = $income->getOneSkladOrCreate();
        $incomeSklad->incomeInWarehouse();

        $this->assertEquals(10, $incomeSklad->getQuantity());
        $this->assertEquals(10, $incomeSklad->getQuantityIn());
        $this->assertEquals(0, $incomeSklad->getQuantityPath());
    }
}