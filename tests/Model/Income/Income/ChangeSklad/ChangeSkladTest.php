<?php

namespace App\Tests\Model\Income\Income\ChangeSklad;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ChangeSkladTest extends TestCase
{
    public function testChangeSklad(): void
    {
        $income = (new IncomeBuilder())->withIncomeSklad()->build();
        $zapSklad = $this->createMock(ZapSklad::class);
        $zapSklad->method('getId')->willReturn(1);

        $income->changeSklad($zapSklad);

        $this->assertEquals(1, $income->getFirstSklad()->getZapSklad()->getId());
    }

    public function testChangeSkladWithReserve(): void
    {
        $manager = $this->createMock(Manager::class);
        $user = $this->createMock(User::class);
        $income = (new IncomeBuilder())->withIncomeSklad()->withOrderGood($manager, $user, true)->build();

        $zapSklad = $this->createMock(ZapSklad::class);
        $zapSklad->method('getId')->willReturn(1);

        $income->changeSklad($zapSklad);

        $this->assertEquals(1, $income->getFirstSklad()->getZapSklad()->getId());
        foreach ($income->getZapCardReserve() as $zapCardReserve) {
            $this->assertEquals(1, $zapCardReserve->getZapSklad()->getId());
        }
    }
}