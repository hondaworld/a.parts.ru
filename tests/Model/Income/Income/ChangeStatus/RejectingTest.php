<?php

namespace App\Tests\Model\Income\Income\ChangeStatus;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\AlertType\OrderAlertType;
use App\Model\Shop\Entity\DeleteReason\DeleteReason;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class RejectingTest extends TestCase
{
    public function testRejecting(): void
    {
        $manager = $this->createMock(Manager::class);
        $user = $this->createMock(User::class);
        $income = (new IncomeBuilder(2))->withIncomeSklad()->withOrderGood($manager, $user)->build();
        $incomeSklad = $income->getOneSkladOrCreate();
        $deleteReason = new DeleteReason('Тест', false);
        $orderAlertType = $this->createMock(OrderAlertType::class);
        $income->rejecting($incomeSklad, $deleteReason, $orderAlertType);

        $orderGood = $income->getFirstOrderGood();

        $this->assertEquals(0, $income->getQuantityPath());
        $this->assertEquals(0, $incomeSklad->getQuantityPath());
        $this->assertEquals(0, $income->getReserve());
        $this->assertEquals(0, $incomeSklad->getReserve());
        $this->assertCount(0, $income->getZapCardReserve());

        $this->assertCount(1, $orderGood->getAlerts());
        $this->assertTrue($orderGood->isDeleted());
        $this->assertEquals($deleteReason, $orderGood->getDeleteReason());
        $this->assertFalse($orderGood->getDeleteReasonEmailed());
//        $this->assertEquals($manager, $orderGood->getDeleteManager());
    }
}