<?php

namespace App\Model\Expense\UseCase\ShippingPlace\Create;

use App\Model\Expense\Entity\Shipping\Shipping;
use App\Model\Expense\Entity\ShippingPlace\ShippingPlace;
use App\Model\Flusher;

class Handler
{
    private Flusher $flusher;

    public function __construct(Flusher $flusher)
    {
        $this->flusher = $flusher;
    }

    public function handle(Command $command, Shipping $shipping): void
    {
        $place = new ShippingPlace($command->number, $command->length, $command->width, $command->height, $command->weight);

        if ($command->photo1) {
            $place->updatePhoto1($command->photo1);
        }

        if ($command->photo2) {
            $place->updatePhoto2($command->photo2);
        }

        $shipping->addPlace($place);
        $this->flusher->flush();
    }
}
