<?php

namespace App\Model\Order\UseCase\Order\Cashier;

use App\Model\Beznal\Entity\Beznal\BeznalRepository;
use App\Model\Contact\Entity\Contact\ContactRepository;
use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\UserRepository;

class Handler
{
    private Flusher $flusher;
    private UserRepository $userRepository;
    private ContactRepository $contactRepository;
    private BeznalRepository $beznalRepository;
    private ExpenseDocumentRepository $expenseDocumentRepository;

    public function __construct(ExpenseDocumentRepository $expenseDocumentRepository, UserRepository $userRepository, ContactRepository $contactRepository, BeznalRepository $beznalRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->userRepository = $userRepository;
        $this->contactRepository = $contactRepository;
        $this->beznalRepository = $beznalRepository;
        $this->expenseDocumentRepository = $expenseDocumentRepository;
    }

    public function handle(Command $command, User $user, Manager $manager): void
    {
        $expenseDocument = $this->expenseDocumentRepository->get($command->expenseDocumentID);

        $expenseDocument->updateCashier(
            $command->user->id ? $this->userRepository->get($command->user->id) : null,
            $command->user->contactID ? $this->contactRepository->get($command->user->contactID) : null,
            $command->user->beznalID ? $this->beznalRepository->get($command->user->beznalID) : null,
        );


        $manager->assignOrderOperation($user, null, "Изменение плательщика накладной");

        $this->flusher->flush();
    }
}
