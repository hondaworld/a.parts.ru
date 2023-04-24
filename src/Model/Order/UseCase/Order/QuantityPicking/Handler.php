<?php

namespace App\Model\Order\UseCase\Order\QuantityPicking;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Flusher;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private OrderGoodRepository $orderGoodRepository;

    public function __construct(
        Flusher             $flusher,
        OrderGoodRepository $orderGoodRepository
    )
    {
        $this->flusher = $flusher;
        $this->orderGoodRepository = $orderGoodRepository;
    }

    public function handle(Command $command, array $expenseSklad): void
    {
        if ($command->quantityPicking > $expenseSklad['quantity'] - $expenseSklad['quantityPicking']) {
            throw new DomainException('Нет такого количества');
        }

        $quantity = $command->quantityPicking;

        foreach ($expenseSklad['expenses'] as $expense) {

            if ($expense['quantity'] - $expense['quantityPicking'] > 0) {
                $orderGood = $this->orderGoodRepository->get($expense['goodID']);
                $quantity = $orderGood->increaseQuantityPickingMaximumAllowing($quantity);
            }
            if ($quantity == 0) break;
        }

        $this->flusher->flush();
    }
}
