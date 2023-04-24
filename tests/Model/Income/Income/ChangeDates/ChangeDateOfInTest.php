<?php

namespace App\Tests\Model\Income\Income\ChangeDates;

use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ChangeDateOfInTest extends TestCase
{
    public function testDateOfIn(): void
    {
        $income = (new IncomeBuilder())->build();
        $now = new \DateTime();
        $income->updateDateOfIn($now);

        $this->assertEquals($now, $income->getDateofin());
    }
}