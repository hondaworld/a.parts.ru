<?php

namespace App\Model\Order\UseCase\ShippingPlace\Create;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\ShippingPlace\ShippingPlace;
use App\Model\Expense\Entity\ShippingStatus\ShippingStatus;
use App\Model\Expense\Entity\ShippingStatus\ShippingStatusRepository;
use App\Model\Flusher;

class Handler
{
    private Flusher $flusher;
    private ShippingStatusRepository $shippingStatusRepository;

    public function __construct(ShippingStatusRepository $shippingStatusRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->shippingStatusRepository = $shippingStatusRepository;
    }

    public function handle(Command $command, ExpenseDocument $expenseDocument): void
    {
        $place = new ShippingPlace($command->number, $command->length, $command->width, $command->height, $command->weight);
        if ($expenseDocument->isPicked()) {
            $shippingStatus = $this->shippingStatusRepository->get(ShippingStatus::PICKED_STATUS);
        } else {
            $shippingStatus = $this->shippingStatusRepository->get(ShippingStatus::PICKING_STATUS);
        }
        $shipping = $expenseDocument->getOrCreateShipping($shippingStatus);
        $shipping->addPlace($place);
        $this->flusher->flush();
    }
}
