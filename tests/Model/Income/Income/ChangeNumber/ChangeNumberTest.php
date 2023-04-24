<?php

namespace App\Tests\Model\Income\Income\ChangeNumber;

use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\Card\ZapCardBuilder;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ChangeNumberTest extends TestCase
{
    public function testChangeZapCard(): void
    {
        $income = (new IncomeBuilder())->build();
        $zapCard = (new ZapCardBuilder('15400PLMA02'))->build();
        $income->updateZapCard($zapCard);

        $this->assertTrue($income->getZapCard()->getNumber()->isEqual($zapCard->getNumber()));
    }

    public function testChangeZapCardWithOrderGoods(): void
    {
        $manager = $this->createMock(Manager::class);
        $user = $this->createMock(User::class);
        $income = (new IncomeBuilder())->withOrderGood($manager, $user)->build();
        $zapCard = (new ZapCardBuilder('15400PLMA02'))->build();

        $numberOld = $income->getZapCard()->getNumber();

        $income->updateZapCard($zapCard);

        foreach ($income->getOrderGoods() as $orderGood) {
            $this->assertTrue($orderGood->getNumber()->isEqual($zapCard->getNumber()));
            $this->assertTrue($orderGood->getNumberOld()->isEqual($numberOld));
        }
    }

    public function testChangeZapCardWithOrderGoods2times(): void
    {
        $manager = $this->createMock(Manager::class);
        $user = $this->createMock(User::class);
        $income = (new IncomeBuilder())->withOrderGood($manager, $user)->build();
        $zapCard = (new ZapCardBuilder('15400PLMA02'))->build();
        $zapCard1 = (new ZapCardBuilder('15400PLMA04'))->build();

        $numberOld = $income->getZapCard()->getNumber();

        $income->updateZapCard($zapCard);
        $income->updateZapCard($zapCard1);

        foreach ($income->getOrderGoods() as $orderGood) {
            $this->assertTrue($orderGood->getNumber()->isEqual($zapCard1->getNumber()));
            $this->assertTrue($orderGood->getNumberOld()->isEqual($numberOld));
        }
    }

    public function testChangeZapCardForDeletedIncome(): void
    {
        $income = (new IncomeBuilder())->build();
        $income->updateStatus(new IncomeStatus(IncomeStatus::FAILURE_USER));
        $zapCard = (new ZapCardBuilder('15400PLMA02'))->build();

        $this->expectExceptionMessage('Приход не должен быть удален и не должен быть на складе');
        $income->updateZapCard($zapCard);

    }

    public function testChangeZapCardWithReserve(): void
    {
        $income = (new IncomeBuilder())->build();
        $income->changeReserve(1);
        $zapCard = (new ZapCardBuilder('15400PLMA02'))->build();

        $this->expectExceptionMessage('Не должно быть резерва');
        $income->updateZapCard($zapCard);

    }

    public function testChangeZapCardSameNumber(): void
    {
        $income = (new IncomeBuilder())->build();
        $income->changeReserve(1);
        $zapCard = (new ZapCardBuilder('15400PLMA03'))->build();

        $this->expectExceptionMessage('Номер не изменен');
        $income->updateZapCard($zapCard);

    }
}