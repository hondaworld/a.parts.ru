<?php

namespace App\Tests\Model\Income\Document\IncomeGood;

use App\Model\Manager\Entity\Manager\Manager;
use App\Tests\Builder\Income\IncomeBuilder;
use App\Tests\Builder\Income\IncomeDocumentBuilder;
use PHPUnit\Framework\TestCase;

class AssignIncomeGoodTest extends TestCase
{
    public function testAssign(): void
    {
        $manager = $this->createMock(Manager::class);
        $incomeDocument = (new IncomeDocumentBuilder($manager))->build();
        $income = (new IncomeBuilder())->withIncomeSklad()->build();
        $incomeDocument->assignIncomeGood($income, $income->getFirstSklad(), $manager, 1, 'Тестовая причина');

        $this->assertCount(1, $incomeDocument->getIncomeGoods());
        foreach ($incomeDocument->getIncomeGoods() as $incomeGood) {
            $this->assertEquals(1, $incomeGood->getQuantity());
            $this->assertEquals('Тестовая причина', $incomeGood->getReturningReason());
            $this->assertEquals($income->getFirstSklad()->getZapSklad(), $incomeGood->getZapSklad());
            $this->assertEquals($income, $incomeGood->getIncome());
            $this->assertEquals($income->getZapCard(), $incomeGood->getZapCard());
        }
    }
}