<?php

namespace App\Tests\Model\Income\Income\ChangeDates;

use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ChangeDateOfZakazTest extends TestCase
{
    public function testDateOfOut(): void
    {
        $income = (new IncomeBuilder())->build();
        $now = new \DateTime();
        $income->updateDateOfZakaz($now);

        $this->assertEquals($now, $income->getDateofzakaz());
    }
}