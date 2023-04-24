<?php

namespace App\Tests\Model\Expense\Document;

use App\Tests\Builder\Beznal\FirmBeznalBuilder;
use App\Tests\Builder\Beznal\UserBeznalBuilder;
use App\Tests\Builder\Contact\FirmContactBuilder;
use App\Tests\Builder\Contact\UserContactBuilder;
use App\Tests\Builder\Expense\ExpenseDocumentBuilder;
use App\Tests\Builder\Firm\FirmBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class GruzForDocumentTest extends TestCase
{
    public function testUser(): void
    {
        $user = (new UserBuilder())->build();
        $contact = (new UserContactBuilder($user))->build();
        $beznal = (new UserBeznalBuilder($user))->build();

        $expenseDocument = (new ExpenseDocumentBuilder($user))->build();

        $this->assertEquals($user, $expenseDocument->getGruzUserForDocument());
        $this->assertEquals($contact, $expenseDocument->getGruzUserContactForDocument());
        $this->assertEquals($beznal, $expenseDocument->getGruzUserBeznalForDocument());
    }

    public function testExpUser(): void
    {
        $user = (new UserBuilder('+715151'))->build();
        $contact = (new UserContactBuilder($user))->build();
        $beznal = (new UserBeznalBuilder($user))->build();

        $expenseDocument = (new ExpenseDocumentBuilder())->withExpUser($user, $contact, $beznal)->build();

        $this->assertEquals($user, $expenseDocument->getGruzUserForDocument());
        $this->assertEquals($contact, $expenseDocument->getGruzUserContactForDocument());
        $this->assertEquals($beznal, $expenseDocument->getGruzUserBeznalForDocument());

        $this->assertEquals($user, $expenseDocument->getCashUserForDocument());
        $this->assertEquals($contact, $expenseDocument->getCashUserContactForDocument());
        $this->assertEquals($beznal, $expenseDocument->getCashUserBeznalForDocument());
    }

    public function testGruzUser(): void
    {
        $user = (new UserBuilder('+715151'))->build();
        $contact = (new UserContactBuilder($user))->build();
        $beznal = (new UserBeznalBuilder($user))->build();

        $userGetter = (new UserBuilder('+722222'))->build();
        $contactGetter = (new UserContactBuilder($userGetter))->build();
        $beznalGetter = (new UserBeznalBuilder($userGetter))->build();

        $expenseDocument = (new ExpenseDocumentBuilder())
            ->withExpUser($user, $contact, $beznal)
            ->withGetterUser($userGetter, $contactGetter, $beznalGetter)
            ->build();

        $this->assertEquals($userGetter, $expenseDocument->getGruzUserForDocument());
        $this->assertEquals($contactGetter, $expenseDocument->getGruzUserContactForDocument());
        $this->assertEquals($beznalGetter, $expenseDocument->getGruzUserBeznalForDocument());
    }

    public function testCashUser(): void
    {
        $user = (new UserBuilder('+715151'))->build();
        $contact = (new UserContactBuilder($user))->build();
        $beznal = (new UserBeznalBuilder($user))->build();

        $userCashier = (new UserBuilder('+722222'))->build();
        $contactCashier = (new UserContactBuilder($userCashier))->build();
        $beznalCashier = (new UserBeznalBuilder($userCashier))->build();

        $expenseDocument = (new ExpenseDocumentBuilder())
            ->withExpUser($user, $contact, $beznal)
            ->withCashierUser($userCashier, $contactCashier, $beznalCashier)
            ->build();

        $this->assertEquals($userCashier, $expenseDocument->getGruzUserForDocument());
        $this->assertEquals($contactCashier, $expenseDocument->getGruzUserContactForDocument());
        $this->assertEquals($beznalCashier, $expenseDocument->getGruzUserBeznalForDocument());
    }

    public function testFirm(): void
    {
        $firm = (new FirmBuilder(true))->build();
        $contact = (new FirmContactBuilder($firm))->build();
        $beznal = (new FirmBeznalBuilder($firm))->build();

        $expenseDocument = (new ExpenseDocumentBuilder())->build();
        $this->assertNull($expenseDocument->getGruzFirmForDocument());

        $expenseDocument->updateFirm($firm);
        $this->assertNull($expenseDocument->getGruzFirmForDocument());

        $expenseDocument->updateExpFirm($firm, $contact, $beznal);
        $this->assertEquals($firm, $expenseDocument->getGruzFirmForDocument());
        $this->assertEquals($contact, $expenseDocument->getGruzFirmContactForDocument());
        $this->assertEquals($beznal, $expenseDocument->getGruzFirmBeznalForDocument());
    }

    public function testFirmGruz(): void
    {
        $firm = (new FirmBuilder(true))->build();
        $contact = (new FirmContactBuilder($firm))->build();
        $beznal = (new FirmBeznalBuilder($firm))->build();

        $firmGruz = (new FirmBuilder(true, 'Организация для отгрузки'))->build();
        $contactGruz = (new FirmContactBuilder($firmGruz))->build();
        $beznalGruz = (new FirmBeznalBuilder($firmGruz))->build();

        $expenseDocument = (new ExpenseDocumentBuilder())
            ->withExpFirm($firm, $contact, $beznal)
            ->withGetterFirm($firmGruz, $contactGruz, $beznalGruz)
            ->build();

        $this->assertEquals($firmGruz, $expenseDocument->getGruzFirmForDocument());
        $this->assertEquals($contactGruz, $expenseDocument->getGruzFirmContactForDocument());
        $this->assertEquals($beznalGruz, $expenseDocument->getGruzFirmBeznalForDocument());
    }
}