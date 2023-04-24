<?php

namespace App\Model\Expense\UseCase\Shipping\Delivery;

use App\Model\Expense\Entity\Shipping\ShippingRepository;
use App\Model\Flusher;
use App\Model\Shop\Entity\DeliveryTk\DeliveryTkRepository;

class Handler
{
    private Flusher $flusher;
    private ShippingRepository $shippingRepository;
    private DeliveryTkRepository $deliveryTkRepository;

    public function __construct(
        ShippingRepository       $shippingRepository,
        DeliveryTkRepository     $deliveryTkRepository,
        Flusher                  $flusher
    )
    {
        $this->flusher = $flusher;
        $this->shippingRepository = $shippingRepository;
        $this->deliveryTkRepository = $deliveryTkRepository;
    }

    public function handle(Command $command): void
    {
        $shipping = $this->shippingRepository->get($command->shippingID);

        $shipping->updateDelivery(
            $command->dateofadded,
            $command->delivery_tkID ? $this->deliveryTkRepository->get($command->delivery_tkID) : null,
            $command->tracknumber,
            $command->pay_type
        );

        $this->flusher->flush();
    }
}
