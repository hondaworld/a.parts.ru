<?php

namespace App\Tests\Model\Expense\Document;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Tests\Builder\Beznal\UserBeznalBuilder;
use App\Tests\Builder\Contact\UserContactBuilder;
use App\Tests\Builder\User\FirmcontrBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UpdateGetterTest extends TestCase
{
    public function testUpdateUser(): void
    {
        $firmContr = (new FirmcontrBuilder())->build();
        $user = (new UserBuilder())->build();
        $getter = (new UserBuilder('+7111111'))->build();
        $contact = (new UserContactBuilder($getter))->build();
        $beznal = (new UserBeznalBuilder($getter))->build();

        $expenseDocument = new ExpenseDocument($user);
        $expenseDocument->updateGetterFirmContr($firmContr);

        $expenseDocument->updateGetter($getter, $contact, $beznal);

        $this->assertEquals($getter, $expenseDocument->getGruzUser());
        $this->assertEquals($contact, $expenseDocument->getGruzUserContact());
        $this->assertEquals($beznal, $expenseDocument->getGruzUserBeznal());
        $this->assertNull($expenseDocument->getGruzFirmContr());

        $expenseDocument->updateGetterFirmContr(null);

        $this->assertEquals($getter, $expenseDocument->getGruzUser());
        $this->assertEquals($contact, $expenseDocument->getGruzUserContact());
        $this->assertEquals($beznal, $expenseDocument->getGruzUserBeznal());
        $this->assertNull($expenseDocument->getGruzFirmContr());
    }

    public function testUpdateFirmcontr(): void
    {
        $firmContr = (new FirmcontrBuilder())->build();
        $user = (new UserBuilder())->build();
        $getter = (new UserBuilder('+7111111'))->build();
        $contact = (new UserContactBuilder($getter))->build();
        $beznal = (new UserBeznalBuilder($getter))->build();

        $expenseDocument = new ExpenseDocument($user);
        $expenseDocument->updateGetter($getter, $contact, $beznal);

        $expenseDocument->updateGetterFirmContr($firmContr);

        $this->assertNull($expenseDocument->getGruzUser());
        $this->assertNull($expenseDocument->getGruzUserContact());
        $this->assertNull($expenseDocument->getGruzUserBeznal());
        $this->assertEquals($firmContr, $expenseDocument->getGruzFirmContr());

        $expenseDocument->updateGetter(null, $contact, $beznal);

        $this->assertNull($expenseDocument->getGruzUser());
        $this->assertEquals($contact, $expenseDocument->getGruzUserContact());
        $this->assertEquals($beznal, $expenseDocument->getGruzUserBeznal());
        $this->assertEquals($firmContr, $expenseDocument->getGruzFirmContr());
    }
}