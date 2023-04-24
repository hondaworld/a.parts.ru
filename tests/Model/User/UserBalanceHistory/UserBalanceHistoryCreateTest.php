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

class UserBalanceHistoryCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $manager = (new ManagerBuilder())->build();
        $user = (new UserBuilder())->build();
        $firm = (new FirmBuilder(false))->build();
        $financeType = new FinanceType('Тип', $firm, false);
        $schet = (new SchetBuilder())->build();
        $expenseDocument = (new ExpenseDocumentBuilder())->build();

        $user->assignBalanceHistory(100, $financeType, $manager, $firm, $schet, $expenseDocument, 'Описание');

        $balanceHistory = $user->getBalanceHistory()[0];

        $this->assertEquals(100, $balanceHistory->getBalance());
        $this->assertEquals($financeType, $balanceHistory->getFinanceType());
        $this->assertEquals($manager, $balanceHistory->getManager());
        $this->assertEquals($firm, $balanceHistory->getFirm());
        $this->assertEquals($user, $balanceHistory->getUser());
        $this->assertEquals('Описание', $balanceHistory->getDescription());
        $this->assertEquals($schet, $balanceHistory->getSchet());
        $this->assertEquals($expenseDocument, $balanceHistory->getExpenseDocument());
    }

    public function testCredit(): void
    {
        $manager = (new ManagerBuilder())->build();
        $user = (new UserBuilder())->build();
        $firm = (new FirmBuilder(false))->build();
        $financeType = new FinanceType('Тип', $firm, false);
        $user->credit('100,5', $financeType, $manager, $firm, 'Описание');

        $balanceHistory = $user->getBalanceHistory()[0];

        $this->assertEquals(100.5, $user->getBalance());
        $this->assertEquals(100.5, $balanceHistory->getBalance());
        $this->assertEquals($financeType, $balanceHistory->getFinanceType());
        $this->assertEquals($manager, $balanceHistory->getManager());
        $this->assertEquals($firm, $balanceHistory->getFirm());
        $this->assertEquals($user, $balanceHistory->getUser());
        $this->assertEquals('Описание', $balanceHistory->getDescription());
        $this->assertNull($balanceHistory->getSchet());
        $this->assertNull($balanceHistory->getExpenseDocument());
    }

    public function testCreditBySchet(): void
    {
        $manager = (new ManagerBuilder())->build();
        $user = (new UserBuilder())->build();
        $financeType = new FinanceType('Тип', (new FirmBuilder(false))->build(), false);
        $schet = (new SchetBuilder())->build();

        $user->creditBySchet('100,5', $financeType, $manager, 'Описание', $schet);

        $balanceHistory = $user->getBalanceHistory()[0];

        $this->assertEquals(100.5, $user->getBalance());
        $this->assertEquals(100.5, $balanceHistory->getBalance());
        $this->assertEquals($financeType, $balanceHistory->getFinanceType());
        $this->assertEquals($manager, $balanceHistory->getManager());
        $this->assertEquals($financeType->getFirm(), $balanceHistory->getFirm());
        $this->assertEquals($user, $balanceHistory->getUser());
        $this->assertEquals('Описание', $balanceHistory->getDescription());
        $this->assertEquals($schet, $balanceHistory->getSchet());
        $this->assertNull($balanceHistory->getExpenseDocument());
    }

    public function testDebitByExpense(): void
    {
        $manager = (new ManagerBuilder())->build();
        $user = (new UserBuilder())->build();
        $firm = (new FirmBuilder(false))->build();
        $financeType = new FinanceType('Тип', $firm, false);
        $schet = (new SchetBuilder())->build();
        $expenseDocument = (new ExpenseDocumentBuilder())->build();
        $expenseDocument->reNewDateOfAdded();
        $expenseDocument->updateFinanceData($financeType, null, null);
        $expenseDocument->updateFirm($firm);

        $user->debitByExpense('100,5', '125', $expenseDocument, $manager);

        $balanceHistory = $user->getBalanceHistory()[0];

        $this->assertEquals(-100.5, $user->getBalance());
        $this->assertEquals(-100.5, $balanceHistory->getBalance());
        $this->assertEquals($financeType, $balanceHistory->getFinanceType());
        $this->assertEquals($manager, $balanceHistory->getManager());
        $this->assertEquals($firm, $balanceHistory->getFirm());
        $this->assertEquals($user, $balanceHistory->getUser());
        $this->assertEquals("Списание по документу 125 от " . ($expenseDocument->getDateofadded()->format('d.m.Y')), $balanceHistory->getDescription());
        $this->assertNull($balanceHistory->getSchet());
        $this->assertEquals($expenseDocument, $balanceHistory->getExpenseDocument());
    }
}