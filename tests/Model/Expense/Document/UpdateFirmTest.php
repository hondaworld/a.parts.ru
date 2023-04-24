<?php

namespace App\Tests\Model\Expense\Document;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Tests\Builder\Beznal\FirmBeznalBuilder;
use App\Tests\Builder\Contact\FirmContactBuilder;
use App\Tests\Builder\Firm\FirmBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UpdateFirmTest extends TestCase
{
    public function testUpdateFirm(): void
    {
        $user = (new UserBuilder())->build();
        $firm = (new FirmBuilder(true))->build();

        $expenseDocument = new ExpenseDocument($user);
        $expenseDocument->updateFirm($firm);

        $this->assertEquals($firm, $expenseDocument->getFirm());
    }

    public function testUpdateFirmExp(): void
    {
        $user = (new UserBuilder())->build();
        $firm = (new FirmBuilder(true))->build();
        $contact = (new FirmContactBuilder($firm))->build();
        $beznal = (new FirmBeznalBuilder($firm))->build();

        $expenseDocument = new ExpenseDocument($user);
        $expenseDocument->updateExpFirm($firm, $contact, $beznal);

        $this->assertEquals($firm, $expenseDocument->getExpFirm());
        $this->assertEquals($contact, $expenseDocument->getExpFirmContact());
        $this->assertEquals($beznal, $expenseDocument->getExpFirmBeznal());
    }

    public function testUpdateFirmGruz(): void
    {
        $user = (new UserBuilder())->build();
        $firm = (new FirmBuilder(true))->build();
        $contact = (new FirmContactBuilder($firm))->build();
        $beznal = (new FirmBeznalBuilder($firm))->build();

        $expenseDocument = new ExpenseDocument($user);
        $expenseDocument->updateGruzFirm($firm, $contact, $beznal);

        $this->assertEquals($firm, $expenseDocument->getGruzFirm());
        $this->assertEquals($contact, $expenseDocument->getGruzFirmContact());
        $this->assertEquals($beznal, $expenseDocument->getGruzFirmBeznal());
    }
}