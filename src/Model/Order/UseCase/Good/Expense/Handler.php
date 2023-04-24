<?php

namespace App\Model\Order\UseCase\Good\Expense;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\Sklad\ExpenseSkladRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use App\Service\Detail\Order\OrderReserveService;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private OrderGoodRepository $orderGoodRepository;
    private OrderReserveService $orderReserveService;
    private ExpenseSkladRepository $expenseSkladRepository;

    public function __construct(
        OrderGoodRepository    $orderGoodRepository,
        OrderReserveService    $orderReserveService,
        ExpenseSkladRepository $expenseSkladRepository,
        Flusher                $flusher
    )
    {
        $this->flusher = $flusher;
        $this->orderGoodRepository = $orderGoodRepository;
        $this->orderReserveService = $orderReserveService;
        $this->expenseSkladRepository = $expenseSkladRepository;
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
//                if ($this->expenseFetcher->isExpenseByGoodID($orderGood->getId())) {
//                    throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " уже отгрузке");
//                }
                if (!empty($orderGood->getExpenses())) {
                    throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " уже отгрузке");
                }
                if ($orderGood->getZapSklad()) {
                    $this->orderReserveService->addReserve($orderGood, $manager, true);

                    $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Обработка складской детали", $orderGood->getNumber()->getValue());
                } else {

                    if ($this->expenseSkladRepository->hasAddedByOrderGood($orderGood)) {
                        throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " в перемещении");
                    }

                    if (!$orderGood->getIncome()) {
                        throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " еще не заказана");
                    }

                    if ($orderGood->getIncome()->getQuantityIn() < $orderGood->getQuantity()) {
                        throw new DomainException("Детали " . $orderGood->getNumber()->getValue() . " нет в достаточном количестве");
                    }

                    $incomeSklad = $orderGood->getIncome()->getSkladWithPositiveQuantity();

                    if (!$incomeSklad) {
                        throw new DomainException("Детали " . $orderGood->getNumber()->getValue() . " нет в достаточном количестве");
                    }

                    $orderGood->assignExpense($orderGood->getIncome(), $orderGood->getQuantity());

                    $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Обработка заказной детали", $orderGood->getNumber()->getValue());
                }
            } catch (DomainException $exception) {
                $messages[] = ['type' => 'error', 'message' => $exception->getMessage()];
            }
            $this->flusher->flush();
        }

        return $messages;
    }
}
