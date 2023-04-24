<?php

namespace App\Model\Order\UseCase\Good\ExpenseDelete;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use App\ReadModel\Expense\ExpenseFetcher;
use App\Service\Detail\Order\OrderReserveService;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private OrderGoodRepository $orderGoodRepository;
    private ExpenseFetcher $expenseFetcher;
    private OrderReserveService $orderReserveService;

    public function __construct(
        OrderGoodRepository    $orderGoodRepository,
        ExpenseFetcher         $expenseFetcher,
        OrderReserveService    $orderReserveService,
        Flusher                $flusher
    )
    {
        $this->flusher = $flusher;
        $this->orderGoodRepository = $orderGoodRepository;
        $this->expenseFetcher = $expenseFetcher;
        $this->orderReserveService = $orderReserveService;
    }

    public function handle(Command $command, Manager $manager, ExpenseDocument $expenseDocument): array
    {
        $messages = [];

        if ($expenseDocument->getUser()->isDebt()) {
            throw new DomainException("У клиента просроченная задолженность");
        }

        if ($expenseDocument->isPick()) {
            throw new DomainException("Заказ находится в сборке");
        }

        foreach ($command->cols as $goodID) {
            $orderGood = $this->orderGoodRepository->get($goodID);

            try {
                if (empty($orderGood->getExpenses())) {
                    throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " не в отгрузке");
                }

                if ($orderGood->getZapSklad()) {
                    $orderGood->removeReserve();

                    $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Удаление из обработанных складской детали", $orderGood->getNumber()->getValue());
                } else {
                    $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Удаление из обработанных заказной детали", $orderGood->getNumber()->getValue());
                }

                $orderGood->clearExpenses();
                $orderGood->updateQuantityPicking(0);

            } catch (DomainException $exception) {
                $messages[] = ['type' => 'error', 'message' => $exception->getMessage()];
            }


        }
        $this->flusher->flush();

//        dump($messages);

        return $messages;
    }
}
