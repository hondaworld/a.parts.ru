<?php

namespace App\Model\Order\UseCase\Order\CashierFirmContr;

use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\FirmContr\FirmContrRepository;
use App\Model\User\Entity\User\User;

class Handler
{
    private FirmContrRepository $firmContrRepository;
    private Flusher $flusher;
    private ExpenseDocumentRepository $expenseDocumentRepository;

    public function __construct(ExpenseDocumentRepository $expenseDocumentRepository, FirmContrRepository $firmContrRepository, Flusher $flusher)
    {
        $this->firmContrRepository = $firmContrRepository;
        $this->flusher = $flusher;
        $this->expenseDocumentRepository = $expenseDocumentRepository;
    }

    public function handle(Command $command, User $user, Manager $manager): void
    {
        $expenseDocument = $this->expenseDocumentRepository->get($command->expenseDocumentID);

        $expenseDocument->updateCashierFirmContr(
            $command->firmcontrID ? $this->firmContrRepository->get($command->firmcontrID) : null
        );

        $manager->assignOrderOperation($user, null, "Изменение контрагента плательщика накладной");

        $this->flusher->flush();
    }
}
