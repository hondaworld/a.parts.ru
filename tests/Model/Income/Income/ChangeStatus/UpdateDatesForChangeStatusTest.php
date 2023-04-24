<?php

namespace App\Tests\Model\Income\Income\ChangeStatus;

use App\Model\Income\Entity\Status\IncomeStatus;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class UpdateDatesForChangeStatusTest extends TestCase
{
    public function testDeletedStatus(): void
    {
        $incomeStatus = new IncomeStatus(IncomeStatus::FAILURE_USER);
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->build();
        $now = new \DateTime();
        $income->updateDatesForChangeStatus($incomeStatus, $now, null);
        $this->assertNull($income->getDateofzakaz());
        $this->assertNull($income->getDateofout());
        $this->assertNull($income->getDateofinplan());
    }

    public function testDateOfZakaz(): void
    {
        $incomeStatus = new IncomeStatus(IncomeStatus::PURCHASED);
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->build();
        $now = new \DateTime();
        $income->updateDatesForChangeStatus($incomeStatus, $now, null);
        $this->assertEquals($now, $income->getDateofzakaz());
        $this->assertNull($income->getDateofout());
        $this->assertNull($income->getDateofinplan());
    }

    public function testDateOfZakazIfNotNull(): void
    {
        $incomeStatus = new IncomeStatus(IncomeStatus::PURCHASED);
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->build();
        $now = new \DateTime();
        $newDate = new \DateTime('+1 day');
        $income->updateDateOfZakaz($newDate);
        $income->updateDatesForChangeStatus($incomeStatus, $now, null);
        $this->assertEquals($newDate, $income->getDateofzakaz());
        $this->assertNull($income->getDateofout());
        $this->assertNull($income->getDateofinplan());
    }

    public function testDateOfOut(): void
    {
        $incomeStatus = new IncomeStatus(IncomeStatus::IN_PATH);
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->build();
        $now = new \DateTime();
        $income->updateDatesForChangeStatus($incomeStatus, $now, null);
        $this->assertEquals($now, $income->getDateofzakaz());
        $this->assertEquals($now, $income->getDateofout());
        $this->assertEquals($now, $income->getDateofinplan());
    }

    public function testDateOfOutNotNull(): void
    {
        $incomeStatus = new IncomeStatus(IncomeStatus::IN_PATH);
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->build();
        $now = new \DateTime();
        $newDate = new \DateTime('+1 day');
        $income->updateDateOfOut($newDate);
        $income->updateDatesForChangeStatus($incomeStatus, $now, null);
        $this->assertEquals($now, $income->getDateofzakaz());
        $this->assertEquals($newDate, $income->getDateofout());
        $this->assertEquals($now, $income->getDateofinplan());
    }

    public function testDateOfPlanNotNull(): void
    {
        $incomeStatus = new IncomeStatus(IncomeStatus::IN_PATH);
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->build();
        $now = new \DateTime();
        $newDate = new \DateTime('+1 day');
        $income->updateDatesForChangeStatus($incomeStatus, $now, $newDate);
        $this->assertEquals($now, $income->getDateofzakaz());
        $this->assertEquals($now, $income->getDateofout());
        $this->assertEquals($newDate, $income->getDateofinplan());
    }
}