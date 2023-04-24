<?php

namespace App\Model\Order\UseCase\Good\ReserveDelete;

use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use App\Service\Detail\Order\OrderReserveService;
use Doctrine\DBAL\Exception;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private OrderGoodRepository $orderGoodRepository;
    private OrderReserveService $orderReserveService;

    public function __construct(
        OrderGoodRepository    $orderGoodRepository,
        OrderReserveService    $orderReserveService,
        Flusher                $flusher
    )
    {
        $this->flusher = $flusher;
        $this->orderGoodRepository = $orderGoodRepository;
        $this->orderReserveService = $orderReserveService;
    }

    public function handle(Command $command, Manager $manager): array
    {
        $messages = [];

        foreach ($command->cols as $goodID) {
            $orderGood = $this->orderGoodRepository->get($goodID);

            try {
                if (!empty($orderGood->getExpenses())) {
                    throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " уже в отгрузке");
                }

                if ($orderGood->getZapSklad()) {
                    $orderGood->removeReserve();

                    $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Удаление из резерва складской детали", $orderGood->getNumber()->getValue());
                } else {
                    $this->orderReserveService->removeReserveIncomeByOrderGood($orderGood);

                    $manager->assignOrderOperation($orderGood->getOrder()->getUser(), $orderGood->getOrder(), "Удаление из резерва заказной детали", $orderGood->getNumber()->getValue());
                }
            } catch (DomainException | Exception $exception) {
                $messages[] = ['type' => 'error', 'message' => $exception->getMessage()];
            }


        }

//        if ($command->zapSkladID == $orderGood->getZapSklad()->getId()) {
//            throw new \DomainException('Вы не можете выбрать тот же склад');
//        }
        $this->flusher->flush();

//        dump($messages);

        return $messages;
    }
}
