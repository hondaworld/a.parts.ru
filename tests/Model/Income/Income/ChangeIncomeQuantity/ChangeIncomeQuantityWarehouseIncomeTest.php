<?php

namespace App\Tests\Model\Income\Income\ChangeIncomeQuantity;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ChangeIncomeQuantityWarehouseIncomeTest extends TestCase
{
    public function testQuantityMore(): void
    {
        $manager = $this->createMock(Manager::class);
        $user = $this->createMock(User::class);
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->withOrderGood($manager, $user)->build();

        $this->expectExceptionMessage('Нельзя увеличить количество в заказной детали');
        $income->changeIncomeQuantity(15);
    }

    public function testQuantityLess(): void
    {
        $manager = $this->createMock(Manager::class);
        $user = $this->createMock(User::class);
        $income = (new IncomeBuilder(10, 0, 10, 10, 0))->withOrderGood($manager, $user)->build();
        $income->changeIncomeQuantity(3);
        $this->assertEquals(3, $income->getQuantity());
        $this->assertEquals(3, $income->getQuantityPath());
        $this->assertEquals(3, $income->getReserve());
        $this->assertEquals(0, $income->getQuantityIn());
        $this->assertEquals(0, $income->getQuantityReturn());

        $this->assertCount(2, $income->getFirstOrderGood()->getOrder()->getOrderGoods());

        $this->assertEquals(3, $income->getFirstOrderGood()->getOrder()->getOrderGoods()[0]->getQuantity());
        $this->assertEquals(7, $income->getFirstOrderGood()->getOrder()->getOrderGoods()[1]->getQuantity());
        $this->assertNull($income->getFirstOrderGood()->getOrder()->getOrderGoods()[1]->getIncome());
    }
}