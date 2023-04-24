<?php

namespace App\Model\Order\UseCase\Order\CashierSchetFak;

use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;

class Handler
{
    private Flusher $flusher;
    private ExpenseDocumentRepository $expenseDocumentRepository;

    public function __construct(ExpenseDocumentRepository $expenseDocumentRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->expenseDocumentRepository = $expenseDocumentRepository;
    }

    public function handle(Command $command, User $user, Manager $manager): void
    {
        $expenseDocument = $this->expenseDocumentRepository->get($command->expenseDocumentID);

        $expenseDocument->updateCashierSchetFak($command->isGruzInnKpp);

        $manager->assignOrderOperation($user, null, "Изменение данных счет/фактуры плательщика накладной");

        $this->flusher->flush();
    }
}
