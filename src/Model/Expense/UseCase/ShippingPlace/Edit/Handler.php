<?php

namespace App\Model\Expense\UseCase\ShippingPlace\Edit;

use App\Model\Expense\Entity\ShippingPlace\ShippingPlaceRepository;
use App\Model\Flusher;

class Handler
{
    private ShippingPlaceRepository $repository;
    private Flusher $flusher;

    public function __construct(ShippingPlaceRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $shippingPlace = $this->repository->get($command->shipping_placeID);
        $shippingPlace->update($command->number, $command->length, $command->width, $command->height, $command->weight);

        if ($command->photo1) {
            $shippingPlace->updatePhoto1($command->photo1);
        }

        if ($command->photo2) {
            $shippingPlace->updatePhoto2($command->photo2);
        }

        $this->flusher->flush();
    }
}
