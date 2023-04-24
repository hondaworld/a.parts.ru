<?php

namespace App\Model\Order\UseCase\ExpenseDocument\FinanceType;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $expenseDocumentID;

    /**
     * @var int
     */
    public $finance_typeID;

    /**
     * @var int
     */
    public $expense_type_id;

    /**
     * @var int
     */
    public $reseller_id;

    public function __construct(int $expenseDocumentID)
    {
        $this->expenseDocumentID = $expenseDocumentID;
    }

    public static function fromEntity(ExpenseDocument $expenseDocument): self
    {
        $command = new self($expenseDocument->getId());
        $command->finance_typeID = $expenseDocument->getFinanceType() ? $expenseDocument->getFinanceType()->getId() : null;
        $command->expense_type_id = $expenseDocument->getExpenseType() ? $expenseDocument->getExpenseType()->getId() : null;
        $command->reseller_id = $expenseDocument->getReseller() ? $expenseDocument->getReseller()->getId() : null;
        return $command;
    }
}
