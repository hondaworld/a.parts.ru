<?php

namespace App\Tests\Model\Finance\FinanceType;

use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Tests\Builder\Firm\FirmBuilder;
use PHPUnit\Framework\TestCase;

class FinanceTypeCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $firm = (new FirmBuilder(true))->build();
        $financeType = new FinanceType('Тип', $firm, false);

        $this->assertEquals('Тип', $financeType->getName());
        $this->assertEquals($firm, $financeType->getFirm());
        $this->assertFalse($financeType->isMain());
    }
}