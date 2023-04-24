<?php

namespace App\Model\Order\UseCase\Order\CashierFirmContr;

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
    public $firmcontrID;

    public function __construct(int $expenseDocumentID)
    {
        $this->expenseDocumentID = $expenseDocumentID;
    }

    public static function fromEntity(ExpenseDocument $expenseDocument): self
    {
        $command = new self($expenseDocument->getId());
        $command->firmcontrID = $expenseDocument->getCashFirmContr() ? $expenseDocument->getCashFirmContr()->getId() : null;
        return $command;
    }
}
