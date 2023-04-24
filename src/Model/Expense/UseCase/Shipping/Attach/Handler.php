<?php

namespace App\Model\Expense\UseCase\Shipping\Attach;

use App\Model\Expense\Entity\Shipping\ShippingRepository;
use App\Model\Flusher;

class Handler
{
    private ShippingRepository $shippingRepository;
    private Flusher $flusher;

    public function __construct(ShippingRepository $shippingRepository, Flusher $flusher)
    {
        $this->shippingRepository = $shippingRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $shipping = $this->shippingRepository->get($command->shippingID);
        $shipping->updateNakladnaya($command->nakladnaya);
        $this->flusher->flush();
    }
}
