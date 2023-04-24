<?php

namespace App\Model\Order\UseCase\Good\Refuse;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use App\Model\Shop\Entity\DeleteReason\DeleteReasonRepository;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private OrderGoodRepository $orderGoodRepository;
    private DeleteReasonRepository $deleteReasonRepository;

    public function __construct(
        OrderGoodRepository    $orderGoodRepository,
        DeleteReasonRepository $deleteReasonRepository,
        Flusher                $flusher
    )
    {
        $this->flusher = $flusher;
        $this->orderGoodRepository = $orderGoodRepository;
        $this->deleteReasonRepository = $deleteReasonRepository;
    }

    public function handle(Command $command, Manager $manager): array
    {
        $messages = [];

        $deleteReason = $this->deleteReasonRepository->get($command->deleteReasonID);

        foreach ($command->cols as $goodID) {
            $orderGood = $this->orderGoodRepository->get($goodID);

            try {
                if ($orderGood->getZapSklad()) {
                    if (!empty($orderGood->getExpenses())) {
                        throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " уже в отгрузке");
                    }

                    if (!empty($orderGood->getZapCardReserve())) {
                        throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " в резерве");
                    }

                    $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Удаление складской детали по причине " . $deleteReason->getName(), $orderGood->getNumber()->getValue());
                } else {
                    if ($orderGood->getIncome()) {
                        throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " уже заказана");
                    }

                    $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Удаление заказной детали по причине " . $deleteReason->getName(), $orderGood->getNumber()->getValue());
                }

                $orderGood->deleteGood($deleteReason, $manager);
                $messages[] = ['type' => 'success', 'message' => "Деталь " . $orderGood->getNumber()->getValue() . " удалена"];

            } catch (DomainException $exception) {
                $messages[] = ['type' => 'error', 'message' => $exception->getMessage()];
            }


        }
        $this->flusher->flush();

//        dump($messages);

        return $messages;
    }
}
