<?php

namespace App\Model\Order\UseCase\Good\Price;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGoodRepository;

class Handler
{
    private OrderGoodRepository $repository;
    private Flusher $flusher;

    public function __construct(
        OrderGoodRepository $repository,
        Flusher             $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command, Manager $manager): void
    {
        $orderGood = $this->repository->get($command->goodID);

        $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Изменение цены детали с " . $orderGood->getPrice() . "р. на " . $command->price . "р., скидки с " . $orderGood->getDiscount() . "% на " . $command->discount . "%", $orderGood->getNumber()->getValue());

        $orderGood->updatePrice(
            $command->price,
            $command->discount
        );

        $this->flusher->flush();
    }
}
