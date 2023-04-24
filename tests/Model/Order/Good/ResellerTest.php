<?php

namespace App\Tests\Model\Order\Good;

use App\Model\Shop\Entity\Reseller\Reseller;
use App\Tests\Builder\Expense\ExpenseDocumentBuilder;
use PHPUnit\Framework\TestCase;

class ResellerTest extends TestCase
{
    public function testResellerUpdate()
    {
        $expenseDocument = (new ExpenseDocumentBuilder())->build();

        $this->assertNull($expenseDocument->getReseller());

        $reseller = new Reseller('Тест');
        $expenseDocument->updateReseller($reseller);
        $this->assertEquals($reseller, $expenseDocument->getReseller());

        $expenseDocument->updateReseller(null);
        $this->assertNull($expenseDocument->getReseller());

    }
}