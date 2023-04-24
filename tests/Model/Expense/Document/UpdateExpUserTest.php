<?php

namespace App\Tests\Model\Expense\Document;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Tests\Builder\Beznal\UserBeznalBuilder;
use App\Tests\Builder\Contact\UserContactBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UpdateExpUserTest extends TestCase
{
    public function testUpdate(): void
    {
        $user = (new UserBuilder())->build();

        $userExp = (new UserBuilder('+71111'))->build();
        $contact = (new UserContactBuilder($userExp))->build();
        $beznal = (new UserBeznalBuilder($userExp))->build();

        $expenseDocument = new ExpenseDocument($user);
        $expenseDocument->updateExpUser($userExp, $contact, $beznal);

        $this->assertEquals($userExp, $expenseDocument->getExpUser());
        $this->assertEquals($contact, $expenseDocument->getExpUserContact());
        $this->assertEquals($beznal, $expenseDocument->getExpUserBeznal());
    }
}