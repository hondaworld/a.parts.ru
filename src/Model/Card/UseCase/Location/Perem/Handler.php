<?php

namespace App\Model\Card\UseCase\Location\Perem;

use App\Model\Expense\Entity\Sklad\ExpenseSkladRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\ReadModel\Income\IncomeFetcher;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private IncomeFetcher $incomeFetcher;
    private ZapSkladRepository $zapSkladRepository;
    private ExpenseSkladRepository $expenseSkladRepository;
    private IncomeRepository $incomeRepository;

    public function __construct(
        IncomeFetcher $incomeFetcher,
        IncomeRepository $incomeRepository,
        ZapSkladRepository $zapSkladRepository,
        ExpenseSkladRepository $expenseSkladRepository,
        Flusher $flusher
    )
    {
        $this->flusher = $flusher;
        $this->incomeFetcher = $incomeFetcher;
        $this->zapSkladRepository = $zapSkladRepository;
        $this->expenseSkladRepository = $expenseSkladRepository;
        $this->incomeRepository = $incomeRepository;
    }

    public function handle(Command $command, Manager $manager): void
    {
        if ($command->zapSklad->getId() == $command->zapSkladID_to) {
            throw new DomainException('Склады совпадают');
        }

        // Общее количество на складе
        $quantityInWareHouseAll = $this->incomeFetcher->findQuantityInWarehouseByZapCardAndZapSklad($command->zapCard->getId(), $command->zapSklad->getId());

        if ($quantityInWareHouseAll < $command->quantity) {
            throw new DomainException('Нет достаточного количества ' . $command->zapCard->getNumber()->getValue() . ' на складе ' . $command->zapSklad->getNameShort());
        }

        $zapSklad_to = $this->zapSkladRepository->get($command->zapSkladID_to);

        // Если деталь уже добавлена в отгрузку, то придется ее оттуда убирать и добавлять заново нужное количество
        if ($this->expenseSkladRepository->hasAdded($command->zapCard, $command->zapSklad, $zapSklad_to)) {
            throw new DomainException('Деталь ' . $command->zapCard->getNumber()->getValue() . ' уже в отгрузке');
        }

        // Получаем все приходы, на которых есть что-то на складе
        $incomes = $this->incomeRepository->findInWarehouseByZapCardAndZapSklad($command->zapCard, $command->zapSklad);

        $quantity = $command->quantity;
        foreach ($incomes as $income) {
            // Получаем единственный склад из выборки
            $incomeSklad = $income->getSklads()[0];
            $quantityInWareHouse = $income->getSklads()[0]->getQuantityInWarehouse();
            if ($quantityInWareHouse >= $quantity) {
                $quantityInWareHouse = $quantity;
                $quantity = 0;
            } else {
                $quantity -= $quantityInWareHouse;
            }
            $income->addReserveByZapSklad($incomeSklad, $zapSklad_to, $quantityInWareHouse, $manager);
            if ($quantity == 0) break;
        }

        $this->flusher->flush();
    }
}
