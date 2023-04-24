<?php

namespace App\Tests\Model\Expense\Document;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Tests\Builder\Beznal\UserBeznalBuilder;
use App\Tests\Builder\Contact\UserContactBuilder;
use App\Tests\Builder\User\FirmcontrBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class ExpenseDocumentCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $user = (new UserBuilder())->build();
        $contact = (new UserContactBuilder($user))->build();
        $beznal = (new UserBeznalBuilder($user))->build();

        $expenseDocument = new ExpenseDocument($user);

        $this->assertEquals($user, $expenseDocument->getUser());
        $this->assertEquals($user, $expenseDocument->getExpUser());
        $this->assertEquals($contact, $expenseDocument->getExpUserContact());
        $this->assertEquals($beznal, $expenseDocument->getExpUserBeznal());
        $this->assertNull($expenseDocument->getGruzUser());
        $this->assertNull($expenseDocument->getGruzUserContact());
        $this->assertNull($expenseDocument->getGruzUserBeznal());
        $this->assertNull($expenseDocument->getGruzFirmcontr());
        $this->assertNull($expenseDocument->getCashUser());
        $this->assertNull($expenseDocument->getCashUserContact());
        $this->assertNull($expenseDocument->getCashUserBeznal());
        $this->assertNull($expenseDocument->getCashFirmcontr());
        $this->assertFalse($expenseDocument->isGruzInnKpp());
    }

    public function testCreateFirmcontr(): void
    {
        $user = (new UserBuilder())->build();
        $firmcontr1 = (new FirmcontrBuilder('Контрагент 1'))->build();
        $firmcontr2 = (new FirmcontrBuilder('Контрагент 2'))->build();

        $user->updateGetterFirmContr($firmcontr1);
        $user->updateCashierFirmContr($firmcontr2);

        $expenseDocument = new ExpenseDocument($user);

        $this->assertEquals($user, $expenseDocument->getUser());
        $this->assertNull($expenseDocument->getGruzUser());
        $this->assertNull($expenseDocument->getGruzUserContact());
        $this->assertNull($expenseDocument->getGruzUserBeznal());
        $this->assertEquals($firmcontr1, $expenseDocument->getGruzFirmcontr());
        $this->assertNull($expenseDocument->getCashUser());
        $this->assertNull($expenseDocument->getCashUserContact());
        $this->assertNull($expenseDocument->getCashUserBeznal());
        $this->assertEquals($firmcontr2, $expenseDocument->getCashFirmcontr());
        $this->assertFalse($expenseDocument->isGruzInnKpp());
    }

    public function testCreateGetter(): void
    {
        $user = (new UserBuilder())->build();

        $getter = (new UserBuilder('+7111111'))->build();
        $contact = (new UserContactBuilder($getter))->build();
        $beznal = (new UserBeznalBuilder($getter))->build();

        $user->updateGetter($getter, $contact, $beznal);
        $expenseDocument = new ExpenseDocument($user);

        $this->assertEquals($user, $expenseDocument->getUser());
        $this->assertEquals($getter, $expenseDocument->getGruzUser());
        $this->assertEquals($contact, $expenseDocument->getGruzUserContact());
        $this->assertEquals($beznal, $expenseDocument->getGruzUserBeznal());
        $this->assertNull($expenseDocument->getGruzFirmcontr());
        $this->assertNull($expenseDocument->getCashUser());
        $this->assertNull($expenseDocument->getCashUserContact());
        $this->assertNull($expenseDocument->getCashUserBeznal());
        $this->assertNull($expenseDocument->getCashFirmcontr());
        $this->assertFalse($expenseDocument->isGruzInnKpp());
    }

    public function testCreateCashier(): void
    {
        $user = (new UserBuilder())->build();

        $cashier = (new UserBuilder('+7111111'))->build();
        $contact = (new UserContactBuilder($cashier))->build();
        $beznal = (new UserBeznalBuilder($cashier))->build();

        $user->updateCashier($cashier, $contact, $beznal);
        $expenseDocument = new ExpenseDocument($user);

        $this->assertEquals($user, $expenseDocument->getUser());
        $this->assertNull($expenseDocument->getGruzUser());
        $this->assertNull($expenseDocument->getGruzUserContact());
        $this->assertNull($expenseDocument->getGruzUserBeznal());
        $this->assertNull($expenseDocument->getGruzFirmcontr());
        $this->assertEquals($cashier, $expenseDocument->getCashUser());
        $this->assertEquals($contact, $expenseDocument->getCashUserContact());
        $this->assertEquals($beznal, $expenseDocument->getCashUserBeznal());
        $this->assertNull($expenseDocument->getCashFirmcontr());
        $this->assertFalse($expenseDocument->isGruzInnKpp());
    }

    public function testCreateGruzInnKpp(): void
    {
        $user = (new UserBuilder())->build();

        $user->updateCashierSchetFak(true);
        $expenseDocument = new ExpenseDocument($user);

        $this->assertEquals($user, $expenseDocument->getUser());
        $this->assertNull($expenseDocument->getGruzUser());
        $this->assertNull($expenseDocument->getGruzUserContact());
        $this->assertNull($expenseDocument->getGruzUserBeznal());
        $this->assertNull($expenseDocument->getGruzFirmcontr());
        $this->assertNull($expenseDocument->getCashUser());
        $this->assertNull($expenseDocument->getCashUserContact());
        $this->assertNull($expenseDocument->getCashUserBeznal());
        $this->assertNull($expenseDocument->getCashFirmcontr());
        $this->assertTrue($expenseDocument->isGruzInnKpp());
    }
}