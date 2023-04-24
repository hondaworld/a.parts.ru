<?php

namespace App\Model\Expense\UseCase\Sklad\QuantityIncome;

use App\Model\Expense\Entity\Sklad\ExpenseSkladRepository;
use App\Model\Flusher;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private ExpenseSkladRepository $expenseSkladRepository;

    public function __construct(
        Flusher $flusher,
        ExpenseSkladRepository $expenseSkladRepository
    )
    {
        $this->flusher = $flusher;
        $this->expenseSkladRepository = $expenseSkladRepository;
    }

    public function handle(Command $command, array $expenseSklad, array $expenses): void
    {
        if ($command->quantityIncome > $expenseSklad['quantity'] - $expenseSklad['quantityIncome']) {
            throw new DomainException('Нет такого количества');
        }

        $quantity = $command->quantityIncome;
        foreach ($expenses as $expense) {
            if ($expense['quantity'] - $expense['quantityIncome'] > 0) {
                if ($quantity > $expense['quantity'] - $expense['quantityIncome']) {
                    $quantityIncome = $expense['quantity'] - $expense['quantityIncome'];
                    $quantity -= $quantityIncome;
                } else {
                    $quantityIncome = $quantity;
                    $quantity = 0;
                }
                $expenseSklad = $this->expenseSkladRepository->get($expense['expenseID']);
                $expenseSklad->increaseQuantityIncome($quantityIncome);
            }
            if ($quantity == 0) break;
        }

        $this->flusher->flush();
    }
}
