<?php

namespace App\Model\Order\UseCase\ExpenseDocument\Reseller;

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
    public $reseller_id;

    public function __construct(int $expenseDocumentID)
    {
        $this->expenseDocumentID = $expenseDocumentID;
    }

    public static function fromEntity(ExpenseDocument $expenseDocument): self
    {
        $command = new self($expenseDocument->getId());
        $command->reseller_id = $expenseDocument->getReseller() ? $expenseDocument->getReseller()->getId() : null;
        return $command;
    }
}
