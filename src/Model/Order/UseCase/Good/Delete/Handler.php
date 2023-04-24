<?php

namespace App\Model\Order\UseCase\Good\Delete;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGood;
use DomainException;

class Handler
{
    private Flusher $flusher;

    public function __construct(
        Flusher             $flusher
    )
    {
        $this->flusher = $flusher;
    }

    public function handle(Command $command, OrderGood $orderGood, Manager $manager): void
    {
        if ($orderGood->getIncome()) {
            throw new DomainException("Деталь уже заказана");
        }

        if (!empty($orderGood->getZapCardReserve())) {
            throw new DomainException("Деталь в резерве");
        }

        if ($orderGood->getExpenseDocument()) {
            throw new DomainException("Деталь уже отгружена");
        }

        if ($orderGood->isDeleted()) {
            throw new DomainException("Деталь удалена");
        }

        if ($orderGood->getOrder()->isMoved()) {
            throw new DomainException("Статус заказа в перенесенных");
        }

        $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Удаление детали по причине " . $command->deleteReason, $orderGood->getNumber()->getValue());
        $orderGood->getOrder()->removeOrderGood($orderGood);

        $this->flusher->flush();
    }
}
