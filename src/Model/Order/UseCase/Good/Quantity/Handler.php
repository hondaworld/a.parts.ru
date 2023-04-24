<?php

namespace App\Model\Order\UseCase\Good\Quantity;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private OrderGoodRepository $orderGoodRepository;

    public function __construct(
        OrderGoodRepository   $orderGoodRepository,
        Flusher               $flusher
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

        $orderGood->updateQuantity($command->quantity);

        $this->flusher->flush();
    }
}
