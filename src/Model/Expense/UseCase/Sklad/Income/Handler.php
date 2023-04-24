<?php

namespace App\Model\Expense\UseCase\Sklad\Income;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Expense\Entity\Sklad\ExpenseSkladRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Income\Entity\Status\IncomeStatusRepository;
use App\Model\Order\Entity\AlertType\OrderAlertType;
use App\Model\Order\Entity\AlertType\OrderAlertTypeRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;

class Handler
{
    private Flusher $flusher;
    private ZapSkladRepository $zapSkladRepository;
    private ExpenseSkladRepository $expenseSkladRepository;
    private ZapCardRepository $zapCardRepository;
    private IncomeStatusRepository $incomeStatusRepository;
    private OrderAlertTypeRepository $orderAlertTypeRepository;

    public function __construct(
        ZapCardRepository          $zapCardRepository,
        ZapSkladRepository         $zapSkladRepository,
        ExpenseSkladRepository     $expenseSkladRepository,
        IncomeStatusRepository     $incomeStatusRepository,
        OrderAlertTypeRepository   $orderAlertTypeRepository,
        Flusher                    $flusher
    )
    {
        $this->flusher = $flusher;
        $this->zapSkladRepository = $zapSkladRepository;
        $this->expenseSkladRepository = $expenseSkladRepository;
        $this->zapCardRepository = $zapCardRepository;
        $this->incomeStatusRepository = $incomeStatusRepository;
        $this->orderAlertTypeRepository = $orderAlertTypeRepository;
    }

    public function handle(int $zapCardID, int $zapSkladID, int $zapSkladID_to): void
    {
        $zapCard = $this->zapCardRepository->get($zapCardID);
        $zapSklad = $this->zapSkladRepository->get($zapSkladID);
        $zapSklad_to = $this->zapSkladRepository->get($zapSkladID_to);

        $expenses = $this->expenseSkladRepository->findSent($zapCard, $zapSklad, $zapSklad_to);
        foreach ($expenses as $expense) {
            $expense->incomeOnSklad();

            if ($expense->getOrderGood()) {
                $orderGood = $expense->getOrderGood();
                $orderGood->assignAlert($this->orderAlertTypeRepository->get(OrderAlertType::MOVING));
                $orderGood->updateLastIncomeStatus($this->incomeStatusRepository->get(IncomeStatus::IN_WAREHOUSE));
                $orderGood->shippedOnSklad($expense);
            } else {
                $expense->shippedOnSklad();
            }
            $this->flusher->flush();
        }
    }
}
