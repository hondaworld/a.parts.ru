<?php

namespace App\Model\Order\UseCase\Order\Osn;

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
    public $name;

    /**
     * @var string
     */
    public $number;

    /**
     * @var \DateTime
     * @Assert\Type("\DateTime")
     */
    public $dateofadded;

    public function __construct(int $expenseDocumentID)
    {
        $this->expenseDocumentID = $expenseDocumentID;
    }

    public static function fromEntity(ExpenseDocument $expenseDocument): self
    {
        $command = new self($expenseDocument->getId());
        $command->name = $expenseDocument->getOsn()->getName();
        $command->number = $expenseDocument->getOsn()->getNumber();
        $command->dateofadded = $expenseDocument->getOsn()->getDateofadded();
        return $command;
    }
}
