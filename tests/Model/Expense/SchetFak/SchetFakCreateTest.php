<?php

namespace App\Tests\Model\Expense\SchetFak;

use App\Model\Expense\Entity\SchetFak\Document;
use App\Model\Expense\Entity\SchetFak\SchetFak;
use App\Model\Finance\Entity\Nalog\Nalog;
use App\Tests\Builder\Expense\ExpenseDocumentBuilder;
use App\Tests\Builder\Firm\FirmBuilder;
use PHPUnit\Framework\TestCase;

class SchetFakCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $firm = (new FirmBuilder(true))->build();
        $expenseDocument = (new ExpenseDocumentBuilder())->withFirm($firm)->build();
        $nalog = new Nalog('Налог');
        $document = new Document(3, 'pre', 'suf');

        $schetFak = new SchetFak($expenseDocument, $document, $nalog, $expenseDocument->getFirm());

        $this->assertEquals($expenseDocument, $schetFak->getExpenseDocument());
        $this->assertEquals(3, $schetFak->getDocument()->getNum());
        $this->assertEquals('pre', $schetFak->getDocument()->getPrefix());
        $this->assertEquals('suf', $schetFak->getDocument()->getSufix());
        $this->assertEquals($nalog, $schetFak->getNalog());
        $this->assertEquals($firm, $schetFak->getFirm());
    }
}