<?php

namespace App\Model\User\UseCase\ShopPayType\Create;

use App\Model\Flusher;
use App\Model\User\Entity\ShopPayType\ShopPayType;
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
        $shopPayType = new ShopPayType($command->name);

        $this->repository->add($shopPayType);

        $this->flusher->flush();
    }
}
