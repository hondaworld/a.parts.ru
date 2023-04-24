<?php

namespace App\Model\Order\UseCase\Good\Discount;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGoodRepository;

class Handler
{
    private Flusher $flusher;
    private OrderGoodRepository $orderGoodRepository;

    public function __construct(
        OrderGoodRepository    $orderGoodRepository,
        Flusher                $flusher
    )
    {
        $this->flusher = $flusher;
        $this->orderGoodRepository = $orderGoodRepository;
    }

    public function handle(Command $command, Manager $manager): void
    {
        foreach ($command->cols as $goodID) {
            $orderGood = $this->orderGoodRepository->get($goodID);

            $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Изменение скидки с " . $orderGood->getDiscount() . "% на " . $command->discount . "%", $orderGood->getNumber()->getValue());

            $orderGood->updatePrice($orderGood->getPrice(), $command->discount);
        }
        $this->flusher->flush();
    }
}
