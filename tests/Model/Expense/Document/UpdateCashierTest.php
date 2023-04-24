<?php

namespace App\Tests\Model\Expense\Document;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Tests\Builder\Beznal\UserBeznalBuilder;
use App\Tests\Builder\Contact\UserContactBuilder;
use App\Tests\Builder\User\FirmcontrBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UpdateCashierTest extends TestCase
{
    public function testUpdateUser(): void
    {
        $firmContr = (new FirmcontrBuilder())->build();
        $user = (new UserBuilder())->build();
        $cashier = (new UserBuilder('+7111111'))->build();
        $contact = (new UserContactBuilder($cashier))->build();
        $beznal = (new UserBeznalBuilder($cashier))->build();

        $expenseDocument = new ExpenseDocument($user);
        $expenseDocument->updateCashierFirmContr($firmContr);

        $expenseDocument->updateCashier($cashier, $contact, $beznal);

        $this->assertEquals($cashier, $expenseDocument->getCashUser());
        $this->assertEquals($contact, $expenseDocument->getCashUserContact());
        $this->assertEquals($beznal, $expenseDocument->getCashUserBeznal());
        $this->assertNull($expenseDocument->getCashFirmContr());

        $expenseDocument->updateCashierFirmContr(null);

        $this->assertEquals($cashier, $expenseDocument->getCashUser());
        $this->assertEquals($contact, $expenseDocument->getCashUserContact());
        $this->assertEquals($beznal, $expenseDocument->getCashUserBeznal());
        $this->assertNull($expenseDocument->getCashFirmContr());
    }

    public function testUpdateFirmcontr(): void
    {
        $firmContr = (new FirmcontrBuilder())->build();
        $user = (new UserBuilder())->build();
        $cashier = (new UserBuilder('+7111111'))->build();
        $contact = (new UserContactBuilder($cashier))->build();
        $beznal = (new UserBeznalBuilder($cashier))->build();

        $expenseDocument = new ExpenseDocument($user);
        $expenseDocument->updateCashier($cashier, $contact, $beznal);

        $expenseDocument->updateCashierFirmContr($firmContr);

        $this->assertNull($expenseDocument->getCashUser());
        $this->assertNull($expenseDocument->getCashUserContact());
        $this->assertNull($expenseDocument->getCashUserBeznal());
        $this->assertEquals($firmContr, $expenseDocument->getCashFirmContr());

        $expenseDocument->updateCashier(null, $contact, $beznal);

        $this->assertNull($expenseDocument->getCashUser());
        $this->assertEquals($contact, $expenseDocument->getCashUserContact());
        $this->assertEquals($beznal, $expenseDocument->getCashUserBeznal());
        $this->assertEquals($firmContr, $expenseDocument->getCashFirmContr());
    }
}