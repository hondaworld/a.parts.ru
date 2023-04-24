<?php

namespace App\Model\Order\UseCase\Order\Sender;

use App\Model\Beznal\Entity\Beznal\BeznalRepository;
use App\Model\Contact\Entity\Contact\ContactRepository;
use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;

class Handler
{
    private Flusher $flusher;
    private FirmRepository $firmRepository;
    private ContactRepository $contactRepository;
    private BeznalRepository $beznalRepository;
    private ExpenseDocumentRepository $expenseDocumentRepository;

    public function __construct(ExpenseDocumentRepository $expenseDocumentRepository, FirmRepository $firmRepository, ContactRepository $contactRepository, BeznalRepository $beznalRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->firmRepository = $firmRepository;
        $this->contactRepository = $contactRepository;
        $this->beznalRepository = $beznalRepository;
        $this->expenseDocumentRepository = $expenseDocumentRepository;
    }

    public function handle(Command $command, User $user, Manager $manager): void
    {
        $expenseDocument = $this->expenseDocumentRepository->get($command->expenseDocumentID);

        $expenseDocument->updateGruzFirm(
            $command->firm->id ? $this->firmRepository->get($command->firm->id) : null,
            $command->firm->contactID ? $this->contactRepository->get($command->firm->contactID) : null,
            $command->firm->beznalID ? $this->beznalRepository->get($command->firm->beznalID) : null,
        );

        $manager->assignOrderOperation($user, null, "Изменение грузоотправителя накладной");

        $this->flusher->flush();
    }
}
