<?php

namespace App\Model\Order\UseCase\Good\Reserve;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Income\Entity\Status\IncomeStatusRepository;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use App\Service\Detail\Order\OrderReserveService;
use App\Service\Price\PartPriceService;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private OrderGoodRepository $orderGoodRepository;
    private ZapCardRepository $zapCardRepository;
    private IncomeRepository $incomeRepository;
    private OrderReserveService $orderReserveService;
    private PartPriceService $partPriceService;
    private IncomeStatusRepository $incomeStatusRepository;

    public function __construct(
        OrderGoodRepository    $orderGoodRepository,
        ZapCardRepository      $zapCardRepository,
        IncomeRepository       $incomeRepository,
        OrderReserveService    $orderReserveService,
        PartPriceService       $partPriceService,
        IncomeStatusRepository $incomeStatusRepository,
        Flusher                $flusher
    )
    {
        $this->flusher = $flusher;
        $this->orderGoodRepository = $orderGoodRepository;
        $this->zapCardRepository = $zapCardRepository;
        $this->incomeRepository = $incomeRepository;
        $this->orderReserveService = $orderReserveService;
        $this->partPriceService = $partPriceService;
        $this->incomeStatusRepository = $incomeStatusRepository;
    }

    public function handle(Command $command, Manager $manager): array
    {
        $messages = [];

        foreach ($command->cols as $goodID) {
            $orderGood = $this->orderGoodRepository->get($goodID);

            try {
                if (!empty($orderGood->getExpenses())) {
                    throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " уже отгрузке");
                }

                if ($orderGood->getZapSklad()) {

                    $this->orderReserveService->addReserve($orderGood, $manager);

                    $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Добавление в резерв складской детали", $orderGood->getNumber()->getValue());

                } else {

                    if ($orderGood->getIncome()) {
                        throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " уже заказана");
                    }

                    $zapCard = $this->zapCardRepository->getOrCreate($orderGood->getNumber(), $orderGood->getCreater());

                    $prices = $this->partPriceService->onePrice($orderGood->getNumber(), $orderGood->getCreater(), $orderGood->getProviderPrice());

                    $income = new Income(
                        $orderGood->getProviderPrice(),
                        $this->incomeStatusRepository->get(IncomeStatus::DEFAULT_STATUS),
                        $zapCard,
                        $orderGood->getQuantity(),
                        $prices['priceZak'],
                        $prices['priceDostUsd'],
                        $prices['priceWithDostRub']
                    );

                    $this->incomeRepository->add($income);

                    $orderGood->updateIncome($income);

                    $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Добавление в резерв заказной детали", $orderGood->getNumber()->getValue());
                }
            } catch (DomainException $exception) {
                $messages[] = ['type' => 'error', 'message' => $exception->getMessage()];
            }

            $this->flusher->flush();
        }

//        if ($command->zapSkladID == $orderGood->getZapSklad()->getId()) {
//            throw new \DomainException('Вы не можете выбрать тот же склад');
//        }


//        dump($messages);

        return $messages;
    }
}
