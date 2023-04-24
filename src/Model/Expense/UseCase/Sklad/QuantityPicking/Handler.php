<?php

namespace App\Model\Expense\UseCase\Sklad\QuantityPicking;

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
        if ($command->quantityPicking > $expenseSklad['quantity'] - $expenseSklad['quantityPicking']) {
            throw new DomainException('Нет такого количества');
        }

        $quantity = $command->quantityPicking;
        foreach ($expenses as $expense) {
            if ($expense['quantity'] - $expense['quantityPicking'] > 0) {
                if ($quantity > $expense['quantity'] - $expense['quantityPicking']) {
                    $quantityPicking = $expense['quantity'] - $expense['quantityPicking'];
                    $quantity -= $quantityPicking;
                } else {
                    $quantityPicking = $quantity;
                    $quantity = 0;
                }
                $expenseSklad = $this->expenseSkladRepository->get($expense['expenseID']);
                $expenseSklad->increaseQuantityPicking($quantityPicking);
            }
            if ($quantity == 0) break;
        }

        $this->flusher->flush();
    }
}
