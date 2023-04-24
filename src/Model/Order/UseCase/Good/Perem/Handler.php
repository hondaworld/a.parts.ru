<?php

namespace App\Model\Order\UseCase\Good\Perem;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\Service\Detail\Order\OrderReserveService;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private OrderGoodRepository $orderGoodRepository;
    private ZapCardRepository $zapCardRepository;
    private ZapSkladRepository $zapSkladRepository;
    private OrderReserveService $orderReserveService;

    public function __construct(
        OrderGoodRepository    $orderGoodRepository,
        ZapCardRepository      $zapCardRepository,
        ZapSkladRepository     $zapSkladRepository,
        OrderReserveService    $orderReserveService,
        Flusher                $flusher
    )
    {
        $this->flusher = $flusher;
        $this->orderGoodRepository = $orderGoodRepository;
        $this->zapCardRepository = $zapCardRepository;
        $this->zapSkladRepository = $zapSkladRepository;
        $this->orderReserveService = $orderReserveService;
    }

    public function handle(Command $command, Manager $manager): array
    {
        $messages = [];

        $zapSklad = $this->zapSkladRepository->get($command->zapSkladID);

        foreach ($command->cols as $goodID) {
            $orderGood = $this->orderGoodRepository->get($goodID);

            try {
//                if ($this->expenseSkladRepository->hasAddedByOrderGood($orderGood)) {
//                    throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " уже перемещении");
//                }

                if (!empty($orderGood->getExpenseSkladsNotIncome())) {
                    throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " уже в перемещении");
                }

                if (!empty($orderGood->getExpenses())) {
                    throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " уже отгрузке");
                }

                if ($orderGood->getZapSklad()) {

                    if ($orderGood->getZapSklad()->getId() == $command->zapSkladID) {
                        throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " уже на складе");
                    }

                    $this->orderReserveService->addReserve($orderGood, $manager, false, true, $zapSklad);

                    $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Добавление складской детали в перемещение", $orderGood->getNumber()->getValue());

                } else {

                    if (!$orderGood->getIncome()) {
                        throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " еще не заказана");
                    }

                    if ($orderGood->getIncome()->getQuantityIn() < $orderGood->getQuantity()) {
                        throw new DomainException("Детали " . $orderGood->getNumber()->getValue() . " нет в достаточном количестве");
                    }

                    $zapCard = $this->zapCardRepository->getByNumberAndCreater($orderGood->getNumber(), $orderGood->getCreater());
                    $incomeSklad = $orderGood->getIncome()->getSkladWithPositiveQuantity();

                    if (!$incomeSklad) {
                        throw new DomainException("Детали " . $orderGood->getNumber()->getValue() . " нет в достаточном количестве");
                    }

                    if ($incomeSklad->getZapSklad()->getId() == $command->zapSkladID) {
                        throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " уже на складе");
                    }

                    $orderGood->assignExpenseSklad($zapCard, $incomeSklad->getZapSklad(), $zapSklad, $orderGood->getIncome(), $orderGood->getQuantity());

                    $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Добавление заказной детали в перемещение", $orderGood->getNumber()->getValue());
                }

                $messages[] = ['type' => 'success', 'message' => "Деталь " . $orderGood->getNumber()->getValue() . " добавлена в перемещение"];

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
