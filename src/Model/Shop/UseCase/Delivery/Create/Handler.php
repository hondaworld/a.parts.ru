<?php

namespace App\Model\Shop\UseCase\Delivery\Create;

use App\Model\Flusher;
use App\Model\Shop\Entity\Delivery\Delivery;
use App\Model\Shop\Entity\Delivery\DeliveryRepository;

class Handler
{
    private DeliveryRepository $repository;
    private Flusher $flusher;

    public function __construct(DeliveryRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        if ($command->isMain) {
            $this->repository->updateMain();
        }

        $delivery = new Delivery(
            $command->name,
            $command->porog,
            $command->x1,
            $command->isPercent1,
            $command->x2,
            $command->isPercent2,
            $command->isTK,
            $command->isOwnDelivery,
            $command->isMain,
            $command->path,
            $this->repository->getNextSort()
        );

        $this->repository->add($delivery);

        $this->flusher->flush();
    }
}
