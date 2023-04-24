<?php

namespace App\Tests\Model\Income\Income\SplitIncomeQuantity;

use App\Model\Income\Entity\Income\Income;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class CloneIncomeForSplitTest extends TestCase
{
    public function testCloneIncome(): void
    {
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->build();
        $incomeNew = Income::cloneFromIncome($income, 15);
        $this->assertEquals(15, $incomeNew->getQuantity());
        $this->assertEquals(15, $incomeNew->getQuantityPath());
        $this->assertEquals(15, $incomeNew->getReserve());
        $this->assertEquals(0, $incomeNew->getQuantityIn());
        $this->assertEquals(0, $incomeNew->getQuantityReturn());
    }

    public function testCloneIncomeSklad(): void
    {
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->withIncomeSklad()->build();
        $incomeNew = Income::cloneFromIncome($income, 3);
        $income->splitIncomeQuantity(7, $incomeNew);

        $this->assertEquals($income->getFirstSklad()->getZapSklad()->getId(), $incomeNew->getFirstSklad()->getZapSklad()->getId());

    }
}