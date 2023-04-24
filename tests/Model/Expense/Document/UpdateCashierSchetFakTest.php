<?php

namespace App\Tests\Model\Expense\Document;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UpdateCashierSchetFakTest extends TestCase
{
    public function testUpdate(): void
    {
        $user = (new UserBuilder())->build();

        $expenseDocument = new ExpenseDocument($user);
        $this->assertFalse($expenseDocument->isGruzInnKpp());

        $expenseDocument->updateCashierSchetFak(true);
        $this->assertTrue($expenseDocument->isGruzInnKpp());

        $expenseDocument->updateCashierSchetFak(false);
        $this->assertFalse($expenseDocument->isGruzInnKpp());
    }
}