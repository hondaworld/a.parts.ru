<?php

namespace App\Model\Order\UseCase\Good\QuantityChange;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private OrderGoodRepository $orderGoodRepository;

    public function __construct(
        OrderGoodRepository      $orderGoodRepository,
        Flusher                  $flusher
    )
    {
        $this->flusher = $flusher;
        $this->orderGoodRepository = $orderGoodRepository;
    }

    public function handle(Command $command, Manager $manager): void
    {
        $orderGood = $this->orderGoodRepository->get($command->goodID);

        if ($orderGood->getExpenses()) {
            throw new DomainException("Деталь уже в отгрузке");
        }

        if ($orderGood->isDeleted()) {
            throw new DomainException("Деталь удалена");
        }

        if ($orderGood->getIncome()) {
            throw new DomainException("Деталь уже заказана");
        }

        if ($orderGood->getZapCardReserve()) {
            throw new DomainException("Деталь в резерве");
        }

        $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Изменение количества с " . $orderGood->getQuantity() . " на " . $command->quantity, $orderGood->getNumber()->getValue());

        $orderGoodNew = $orderGood->splitQuantity($command->quantity, $command->quantity_new);

        $manager->assignOrderOperation($orderGoodNew->getOrder()->getUser(), $orderGoodNew->getOrder(), "Добавление детали", $orderGoodNew->getNumber()->getValue());

        $this->flusher->flush();
    }
}
