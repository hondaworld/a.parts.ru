<?php

namespace App\Model\User\UseCase\ShopPayType\Edit;

use App\Model\Flusher;
use App\Model\User\Entity\ShopPayType\ShopPayTypeRepository;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(ShopPayTypeRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $shopPayType = $this->repository->get($command->shop_pay_typeID);

        $shopPayType->update($command->name);

        $this->flusher->flush();
    }
}
