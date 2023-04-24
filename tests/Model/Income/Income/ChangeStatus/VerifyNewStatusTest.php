<?php

namespace App\Tests\Model\Income\Income\ChangeStatus;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Income\Entity\Status\IncomeStatus;
use PHPUnit\Framework\TestCase;

class VerifyNewStatusTest extends TestCase
{
    public function testInIncomingOnWarehouseNewStatusIsOrdered(): void
    {
        $number = new DetailNumber('15400PLMA03');

        $incomeStatus = new IncomeStatus(IncomeStatus::INCOME_IN_WAREHOUSE);
        $incomeStatusNew = new IncomeStatus(IncomeStatus::ORDERED);

        $message = $incomeStatus->verifyNewStatus($number, $incomeStatusNew);

        $this->assertNull($message);
    }

    public function testInIncomingOnWarehouseNewStatusIsOnTheWay(): void
    {
        $number = new DetailNumber('15400PLMA03');

        $incomeStatus = new IncomeStatus(IncomeStatus::INCOME_IN_WAREHOUSE);
        $incomeStatusNew = new IncomeStatus(IncomeStatus::IN_PATH);

        $message = $incomeStatus->verifyNewStatus($number, $incomeStatusNew);

        $this->assertNull($message);
    }

    public function testInIncomingOnWarehouse(): void
    {
        $number = new DetailNumber('15400PLMA03');

        $incomeStatus = new IncomeStatus(IncomeStatus::INCOME_IN_WAREHOUSE);
        $incomeStatusNew = new IncomeStatus(IncomeStatus::PURCHASED);

        $message = $incomeStatus->verifyNewStatus($number, $incomeStatusNew);

        $this->assertNotNull($message);
    }
}