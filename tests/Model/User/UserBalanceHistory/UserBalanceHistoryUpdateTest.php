<?php

namespace App\Tests\Model\User\UserBalanceHistory;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Tests\Builder\Expense\ExpenseDocumentBuilder;
use App\Tests\Builder\Firm\FirmBuilder;
use App\Tests\Builder\Firm\SchetBuilder;
use App\Tests\Builder\Manager\ManagerBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UserBalanceHistoryUpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $manager = (new ManagerBuilder())->build();
        $user = (new UserBuilder())->build();
        $firm = (new FirmBuilder(false))->build();
        $firm1 = (new FirmBuilder(false, 'Новая фирма'))->build();
        $financeType = new FinanceType('Тип', $firm, false);

        $user->credit('100,5', $financeType, $manager, $firm, 'Описание');

        $balanceHistory = $user->getBalanceHistory()[0];

        $balanceHistory->update('30,25', $firm1, 'Изменение');

        $this->assertEquals(30.25, $user->getBalance());
        $this->assertEquals(30.25, $balanceHistory->getBalance());
        $this->assertEquals($financeType, $balanceHistory->getFinanceType());
        $this->assertEquals($manager, $balanceHistory->getManager());
        $this->assertEquals($firm1, $balanceHistory->getFirm());
        $this->assertEquals($user, $balanceHistory->getUser());
        $this->assertEquals('Изменение', $balanceHistory->getDescription());
        $this->assertNull($balanceHistory->getSchet());
        $this->assertNull($balanceHistory->getExpenseDocument());
    }

    public function testUpdateFinanceType(): void
    {
        $manager = (new ManagerBuilder())->build();
        $user = (new UserBuilder())->build();
        $firm = (new FirmBuilder(false))->build();
        $firm1 = (new FirmBuilder(false, 'Новая фирма'))->build();
        $financeType = new FinanceType('Тип', $firm, false);
        $financeType1 = new FinanceType('Тип 1', $firm1, false);
        $user->credit('100,5', $financeType, $manager, $firm, 'Описание');

        $balanceHistory = $user->getBalanceHistory()[0];

        $balanceHistory->updateFinanceType($firm1, $financeType1);

        $this->assertEquals(100.5, $user->getBalance());
        $this->assertEquals(100.5, $balanceHistory->getBalance());
        $this->assertEquals($financeType1, $balanceHistory->getFinanceType());
        $this->assertEquals($manager, $balanceHistory->getManager());
        $this->assertEquals($firm1, $balanceHistory->getFirm());
        $this->assertEquals($user, $balanceHistory->getUser());
        $this->assertEquals('Описание', $balanceHistory->getDescription());
        $this->assertNull($balanceHistory->getSchet());
        $this->assertNull($balanceHistory->getExpenseDocument());
    }

    public function testUpdateAttach(): void
    {
        $manager = (new ManagerBuilder())->build();
        $user = (new UserBuilder())->build();
        $firm = (new FirmBuilder(false))->build();
        $firm1 = (new FirmBuilder(false, 'Новая фирма'))->build();
        $financeType = new FinanceType('Тип', $firm, false);
        $financeType1 = new FinanceType('Тип 1', $firm1, false);
        $user->credit('100,5', $financeType, $manager, $firm, 'Описание');

        $balanceHistory = $user->getBalanceHistory()[0];
        $this->assertEquals('', $balanceHistory->getAttach());

        $balanceHistory->updateAttach('aaa.pdf');
        $this->assertEquals('aaa.pdf', $balanceHistory->getAttach());
    }
}