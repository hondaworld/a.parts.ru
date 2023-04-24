<?php

namespace App\Model\Order\UseCase\ExpenseDocument\Reseller;

use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Flusher;
use App\Model\Shop\Entity\Reseller\ResellerRepository;

class Handler
{
    private ExpenseDocumentRepository $expenseDocumentRepository;
    private Flusher $flusher;
    private ResellerRepository $resellerRepository;

    public function __construct(ExpenseDocumentRepository $expenseDocumentRepository, ResellerRepository $resellerRepository, Flusher $flusher)
    {
        $this->expenseDocumentRepository = $expenseDocumentRepository;
        $this->flusher = $flusher;
        $this->resellerRepository = $resellerRepository;
    }

    public function handle(Command $command): void
    {
        $user = $this->expenseDocumentRepository->get($command->expenseDocumentID);

        $user->updateReseller(
            $command->reseller_id ? $this->resellerRepository->get($command->reseller_id) : null
        );

        $this->flusher->flush();
    }
}
