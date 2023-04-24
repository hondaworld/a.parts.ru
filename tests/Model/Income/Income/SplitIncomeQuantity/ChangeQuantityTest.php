<?php

namespace App\Tests\Model\Income\Income\SplitIncomeQuantity;

use App\Model\Income\Entity\Income\Income;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ChangeQuantityTest extends TestCase
{
    public function testChangeQuantity(): void
    {
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->build();
        $incomeNew = Income::cloneFromIncome($income, 3);
        $income->splitIncomeQuantity(7, $incomeNew);

        $this->assertEquals(7, $income->getQuantity());
        $this->assertEquals(7, $income->getQuantityPath());
        $this->assertEquals(7, $income->getReserve());
        $this->assertEquals(0, $income->getQuantityIn());
        $this->assertEquals(0, $income->getQuantityReturn());

        $this->assertEquals(3, $incomeNew->getQuantity());
        $this->assertEquals(3, $incomeNew->getQuantityPath());
        $this->assertEquals(3, $incomeNew->getReserve());
        $this->assertEquals(0, $incomeNew->getQuantityIn());
        $this->assertEquals(0, $incomeNew->getQuantityReturn());
    }

    public function testChangeQuantityIncomeSklad(): void
    {
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->withIncomeSklad()->build();
        $incomeNew = Income::cloneFromIncome($income, 3);
        $income->splitIncomeQuantity(7, $incomeNew);

        $this->assertEquals(7, $income->getFirstSklad()->getQuantity());
        $this->assertEquals(7, $income->getFirstSklad()->getQuantityPath());
        $this->assertEquals(7, $income->getFirstSklad()->getReserve());
        $this->assertEquals(0, $income->getFirstSklad()->getQuantityIn());
        $this->assertEquals(0, $income->getFirstSklad()->getQuantityReturn());

        $this->assertEquals(3, $incomeNew->getFirstSklad()->getQuantity());
        $this->assertEquals(3, $incomeNew->getFirstSklad()->getQuantityPath());
        $this->assertEquals(3, $incomeNew->getFirstSklad()->getReserve());
        $this->assertEquals(0, $incomeNew->getFirstSklad()->getQuantityIn());
        $this->assertEquals(0, $incomeNew->getFirstSklad()->getQuantityReturn());
    }

    public function testChangeQuantityOrderGood(): void
    {
        $manager = $this->createMock(Manager::class);
        $user = $this->createMock(User::class);
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->withOrderGood($manager, $user, true)->build();
        $incomeNew = Income::cloneFromIncome($income, 3);
        $income->splitIncomeQuantity(7, $incomeNew);

        $this->assertEquals(7, $income->getFirstOrderGood()->getQuantity());
        $this->assertEquals(3, $incomeNew->getFirstOrderGood()->getQuantity());
    }

    public function testChangeQuantityOrderGoodReserve(): void
    {
        $manager = $this->createMock(Manager::class);
        $user = $this->createMock(User::class);
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->withIncomeSklad()->withOrderGood($manager, $user, true)->build();
        $incomeNew = Income::cloneFromIncome($income, 3);
        $income->splitIncomeQuantity(7, $incomeNew);

        $this->assertEquals(7, $income->getZapCardReserve()[0]->getQuantity());

        $this->assertEquals(3, $incomeNew->getZapCardReserve()[0]->getQuantity());
    }
}