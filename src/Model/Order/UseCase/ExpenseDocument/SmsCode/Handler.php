<?php

namespace App\Model\Order\UseCase\ExpenseDocument\SmsCode;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Flusher;

class Handler
{
    private $flusher;

    public function __construct(Flusher $flusher)
    {
        $this->flusher = $flusher;
    }

    public function handle(Command $command, ExpenseDocument $expenseDocument): void
    {
        if ($expenseDocument->getSmsCode() != $command->sms_code) {
            throw new \DomainException('SMS код введен неверно');
        }

        $expenseDocument->smsCodeChecked();

        $this->flusher->flush();
    }
}
