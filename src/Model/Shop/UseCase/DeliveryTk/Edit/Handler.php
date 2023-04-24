<?php

namespace App\Model\Shop\UseCase\DeliveryTk\Edit;

use App\Model\Flusher;
use App\Model\Shop\Entity\DeliveryTk\DeliveryTkRepository;

class Handler
{
    private DeliveryTkRepository $repository;
    private Flusher $flusher;

    public function __construct(DeliveryTkRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $deliveryTk = $this->repository->get($command->delivery_tkID);

        $deliveryTk->update(
            $command->name,
            $command->http,
            $command->sms_text
        );

        $this->flusher->flush();
    }
}
