<?php

namespace App\Model\Order\UseCase\Good\ExpiredReservesDelete;

use App\Model\Flusher;
use App\Service\Detail\Order\OrderReserveService;

class Handler
{
    private Flusher $flusher;
    private OrderReserveService $orderReserveService;

    public function __construct(
        OrderReserveService    $orderReserveService,
        Flusher                $flusher
    )
    {
        $this->flusher = $flusher;
        $this->orderReserveService = $orderReserveService;
    }

    public function handle(): void
    {
        $this->orderReserveService->removeExpiredReserves();
        $this->flusher->flush();
    }
}
