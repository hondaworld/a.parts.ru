<?php

namespace App\Tests\Model\Income\Income\ChangeQuantity;

use App\Model\Income\Entity\Status\IncomeStatus;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ChangeQuantityExceptionTest extends TestCase
{
    public function testQuantityStatusIsInWarehouseException(): void
    {
        $incomeStatus = $this->createMock(IncomeStatus::class);
        $incomeStatus->method('isInWarehouse')->willReturn(true);
        $income = (new IncomeBuilder())->withIncomeStatus($incomeStatus)->build();
        $income->updateQuantity(10, 0, 10, 10, 0);

        $this->expectExceptionMessage('Деталь удалена или уже на складе');
        $income->changeQuantity(15);
    }

    public function testQuantityStatusIsDeletedException(): void
    {
        $incomeStatus = $this->createMock(IncomeStatus::class);
        $incomeStatus->method('isDeleted')->willReturn(true);
        $income = (new IncomeBuilder())->withIncomeStatus($incomeStatus)->build();
        $income->updateQuantity(10, 0, 10, 10, 0);

        $this->expectExceptionMessage('Деталь удалена или уже на складе');
        $income->changeQuantity(15);
    }
}