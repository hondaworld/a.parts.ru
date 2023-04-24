<?php

namespace App\Model\Order\UseCase\Order\DocumentPrefixes;

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
     * @var string
     */
    public $prefix;

    /**
     * @var string
     */
    public $sufix;

    public function __construct(int $expenseDocumentID)
    {
        $this->expenseDocumentID = $expenseDocumentID;
    }

    public static function fromEntity(ExpenseDocument $expenseDocument): self
    {
        $command = new self($expenseDocument->getId());
        $command->prefix = $expenseDocument->getDocument()->getPrefix();
        $command->sufix = $expenseDocument->getDocument()->getSufix();
        return $command;
    }
}
