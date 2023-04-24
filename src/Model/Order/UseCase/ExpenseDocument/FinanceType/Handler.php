<?php

namespace App\Model\Order\UseCase\ExpenseDocument\FinanceType;

use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Expense\Entity\Type\ExpenseTypeRepository;
use App\Model\Finance\Entity\FinanceType\FinanceTypeRepository;
use App\Model\Flusher;
use App\Model\Shop\Entity\Reseller\ResellerRepository;

class Handler
{
    private $expenseDocumentRepository;
    private $flusher;
    private $financeTypeRepository;
    private $expenseTypeRepository;
    private ResellerRepository $resellerRepository;

    public function __construct(ExpenseDocumentRepository $expenseDocumentRepository, FinanceTypeRepository $financeTypeRepository, ExpenseTypeRepository $expenseTypeRepository, ResellerRepository $resellerRepository, Flusher $flusher)
    {
        $this->expenseDocumentRepository = $expenseDocumentRepository;
        $this->flusher = $flusher;
        $this->financeTypeRepository = $financeTypeRepository;
        $this->expenseTypeRepository = $expenseTypeRepository;
        $this->resellerRepository = $resellerRepository;
    }

    public function handle(Command $command): void
    {
        $user = $this->expenseDocumentRepository->get($command->expenseDocumentID);

        $user->updateFinanceData(
            $command->finance_typeID ? $this->financeTypeRepository->get($command->finance_typeID) : null,
            $command->expense_type_id ? $this->expenseTypeRepository->get($command->expense_type_id) : null,
            $command->reseller_id ? $this->resellerRepository->get($command->reseller_id) : null
        );

        $this->flusher->flush();
    }
}
