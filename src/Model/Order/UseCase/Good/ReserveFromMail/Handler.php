<?php

namespace App\Model\Order\UseCase\Good\ReserveFromMail;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Flusher;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Income\Entity\Status\IncomeStatusRepository;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use App\Model\Order\Entity\Order\Order;
use App\Model\Order\Entity\Order\OrderRepository;
use App\ReadModel\Expense\ExpenseFetcher;
use App\Service\Detail\Order\OrderReserveService;
use App\Service\Price\PartPriceService;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private OrderGoodRepository $orderGoodRepository;
    private ZapCardRepository $zapCardRepository;
    private ExpenseFetcher $expenseFetcher;
    private IncomeRepository $incomeRepository;
    private OrderReserveService $orderReserveService;
    private PartPriceService $partPriceService;
    private IncomeStatusRepository $incomeStatusRepository;
    private ManagerRepository $managerRepository;
    private OrderRepository $orderRepository;
    private CreaterRepository $createrRepository;

    public function __construct(
        OrderGoodRepository    $orderGoodRepository,
        ZapCardRepository      $zapCardRepository,
        ExpenseFetcher         $expenseFetcher,
        IncomeRepository       $incomeRepository,
        OrderReserveService    $orderReserveService,
        PartPriceService       $partPriceService,
        IncomeStatusRepository $incomeStatusRepository,
        ManagerRepository      $managerRepository,
        OrderRepository        $orderRepository,
        CreaterRepository      $createrRepository,
        Flusher                $flusher
    )
    {
        $this->flusher = $flusher;
        $this->orderGoodRepository = $orderGoodRepository;
        $this->zapCardRepository = $zapCardRepository;
        $this->expenseFetcher = $expenseFetcher;
        $this->incomeRepository = $incomeRepository;
        $this->orderReserveService = $orderReserveService;
        $this->partPriceService = $partPriceService;
        $this->incomeStatusRepository = $incomeStatusRepository;
        $this->managerRepository = $managerRepository;
        $this->orderRepository = $orderRepository;
        $this->createrRepository = $createrRepository;
    }

    public function handle(Command $command): array
    {
        $manager = $this->managerRepository->get(Manager::SUPER_ADMIN);

        $order = new Order($command->user, null, null);
        $this->orderRepository->add($order);
        $this->flusher->flush();

        foreach ($command->cols as &$col) {
            try {
                if ($col['createrID'] != 0) {
                    $creater = $this->createrRepository->get($col['createrID']);
                    $orderGood = $this->orderReserveService->addOrderGoodAndReserve($order, new DetailNumber($col['number']), $creater, $command->zapSklad, $col['price'], $col['quantity'] , $manager);
                    $col['reserve'] = $orderGood->getQuantity();
                    $this->flusher->flush();
                } else {
                    throw new DomainException("Производитель не найден");
                }

            } catch (DomainException $exception) {
                $col['comment'] = $exception->getMessage();
                $col['reserve'] = 0;
            }
        }

        return $command->cols;
    }
}
