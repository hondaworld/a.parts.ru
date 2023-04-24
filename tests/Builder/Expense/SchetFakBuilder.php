<?php

namespace App\Tests\Builder\Expense;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\SchetFak\Document;
use App\Model\Expense\Entity\SchetFak\SchetFak;
use App\Model\Finance\Entity\Nalog\Nalog;
use App\Model\Firm\Entity\Firm\Firm;
use App\Tests\Builder\Firm\FirmBuilder;

class SchetFakBuilder
{
    private ExpenseDocument $expenseDocument;
    private Firm $firm;
    private Nalog $nalog;
    private Document $document;

    public function __construct(?ExpenseDocument $expenseDocument = null)
    {
        if ($expenseDocument) {
            $this->expenseDocument = $expenseDocument;
        } else {
            $this->expenseDocument = (new ExpenseDocumentBuilder())->build();
        }
        $this->firm = (new FirmBuilder(true))->build();
        $this->nalog = new Nalog('Налог');
        $this->document = new Document(3, 'pre', 'suf');

    }

    public function build(): SchetFak
    {
        $schetFak = new SchetFak($this->expenseDocument, $this->document, $this->nalog, $this->firm);

        return $schetFak;
    }
}