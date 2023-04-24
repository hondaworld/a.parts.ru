<?php

namespace App\Tests\Model\Finance\FinanceType;

use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Tests\Builder\Firm\FirmBuilder;
use PHPUnit\Framework\TestCase;

class FinanceTypeUpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $firm = (new FirmBuilder(true))->build();
        $firm1 = (new FirmBuilder(true, 'Новая фирма'))->build();
        $financeType = new FinanceType('Тип', $firm, false);

        $financeType->update('Новый тип', $firm1, true);

        $this->assertEquals('Новый тип', $financeType->getName());
        $this->assertEquals($firm1, $financeType->getFirm());
        $this->assertTrue($financeType->isMain());
    }
}