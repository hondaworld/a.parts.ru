<?php

namespace App\Tests\Model\Income\Income\ChangeQuantity;

use App\Model\Income\Entity\Status\IncomeStatus;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ChangeQuantityWithIncomeSkladTest extends TestCase
{
    public function testQuantityMore(): void
    {
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->withIncomeSklad()->build();

        $income->changeQuantity(15);
        $this->assertEquals(15, $income->getQuantity());
        $this->assertEquals(15, $income->getQuantityPath());
        $this->assertEquals(15, $income->getReserve());
        $this->assertEquals(0, $income->getQuantityIn());
        $this->assertEquals(0, $income->getQuantityReturn());

        $this->assertEquals(15, $income->getFirstSklad()->getQuantity());
        $this->assertEquals(15, $income->getFirstSklad()->getQuantityPath());
        $this->assertEquals(15, $income->getFirstSklad()->getReserve());
        $this->assertEquals(0, $income->getFirstSklad()->getQuantityIn());
        $this->assertEquals(0, $income->getFirstSklad()->getQuantityReturn());
    }

    public function testQuantityLess(): void
    {
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->withIncomeSklad()->build();

        $income->changeQuantity(5);
        $this->assertEquals(5, $income->getQuantity());
        $this->assertEquals(5, $income->getQuantityPath());
        $this->assertEquals(5, $income->getReserve());
        $this->assertEquals(0, $income->getQuantityIn());
        $this->assertEquals(0, $income->getQuantityReturn());

        $this->assertEquals(5, $income->getFirstSklad()->getQuantity());
        $this->assertEquals(5, $income->getFirstSklad()->getQuantityPath());
        $this->assertEquals(5, $income->getFirstSklad()->getReserve());
        $this->assertEquals(0, $income->getFirstSklad()->getQuantityIn());
        $this->assertEquals(0, $income->getFirstSklad()->getQuantityReturn());
    }
}