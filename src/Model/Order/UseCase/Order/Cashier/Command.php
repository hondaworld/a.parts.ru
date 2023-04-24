<?php

namespace App\Model\Order\UseCase\Order\Cashier;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\User\UseCase\User\User;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $expenseDocumentID;

    /**
     * @var User
     * @Assert\Valid()
     */
    public $user;

    public $contacts;

    public $beznals;

    public function __construct(int $expenseDocumentID)
    {
        $this->expenseDocumentID = $expenseDocumentID;
    }

    public static function fromEntity(ExpenseDocument $expenseDocument, array $contacts, array $beznals): self
    {
        $command = new self($expenseDocument->getId());
        $command->user = $expenseDocument->getCashUser() ? new User(
            $expenseDocument->getCashUser()->getId(),
            $expenseDocument->getCashUser()->getFullNameWithPhoneMobileAndOrganization(),
            $expenseDocument->getCashUserContact() ? $expenseDocument->getCashUserContact()->getId() : 0,
            $expenseDocument->getCashUserBeznal() ? $expenseDocument->getCashUserBeznal()->getId() : 0
        ) : new User();
        $command->contacts = $contacts;
        $command->beznals = $beznals;
        return $command;
    }
}
