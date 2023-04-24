<?php

namespace App\Model\Order\UseCase\Order\Picking;

use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Expense\Entity\ShippingStatus\ShippingStatus;
use App\Model\Expense\Entity\ShippingStatus\ShippingStatusRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use App\ReadModel\Order\OrderGoodFetcher;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private ExpenseDocumentRepository $expenseDocumentRepository;
    private OrderGoodFetcher $orderGoodFetcher;
    private ShippingStatusRepository $shippingStatusRepository;

    public function __construct(
        ExpenseDocumentRepository $expenseDocumentRepository,
        OrderGoodFetcher          $orderGoodFetcher,
        ShippingStatusRepository  $shippingStatusRepository,
        Flusher                   $flusher
    )
    {
        $this->flusher = $flusher;
        $this->expenseDocumentRepository = $expenseDocumentRepository;
        $this->orderGoodFetcher = $orderGoodFetcher;
        $this->shippingStatusRepository = $shippingStatusRepository;
    }

    public function handle(User $user, Manager $manager): void
    {
        $expenses = $this->orderGoodFetcher->allExpenses($user->getId());
        $shippingStatus = $this->shippingStatusRepository->get(ShippingStatus::PICKING_STATUS);

        if (empty($expenses)) {
            throw new DomainException("В отгрузке отсутствуют детали");
        }

        $expenseDocument = $this->expenseDocumentRepository->getOrCreate($user);
        $expenseDocument->picking();
        $expenseDocument->updateShippingsStatus($shippingStatus);

        $manager->assignOrderOperation($user, null, "Собрать детали");

        $this->flusher->flush();
    }
}
