<?php

namespace App\Tests\Model\Income\Income\ChangeDates;

use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ChangeDateOfInPlanTest extends TestCase
{
    public function testDateOfOut(): void
    {
        $income = (new IncomeBuilder())->build();
        $now = new \DateTime();
        $income->updateDateOfInPlan($now);

        $this->assertEquals($now, $income->getDateofinplan());
    }
}