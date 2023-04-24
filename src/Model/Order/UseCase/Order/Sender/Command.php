<?php

namespace App\Model\Order\UseCase\Order\Sender;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Order\UseCase\Firm;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $expenseDocumentID;

    /**
     * @var Firm
     * @Assert\Valid()
     */
    public $firm;

    public $contacts;

    public $beznals;

    public function __construct(int $expenseDocumentID)
    {
        $this->expenseDocumentID = $expenseDocumentID;
    }

    public static function fromEntity(ExpenseDocument $expenseDocument, array $contacts, array $beznals): self
    {
        $command = new self($expenseDocument->getId());
        $command->firm = $expenseDocument->getGruzFirm() ? new Firm(
            $expenseDocument->getGruzFirm()->getId(),
            $expenseDocument->getGruzFirmContact() ? $expenseDocument->getGruzFirmContact()->getId() : 0,
            $expenseDocument->getGruzFirmBeznal() ? $expenseDocument->getGruzFirmBeznal()->getId() : 0
        ) : new Firm();
        $command->contacts = $contacts;
        $command->beznals = $beznals;
        return $command;
    }
}
