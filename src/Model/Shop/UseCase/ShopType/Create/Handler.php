<?php

namespace App\Model\Shop\UseCase\ShopType\Create;

use App\Model\Flusher;
use App\Model\Shop\Entity\ShopType\ShopType;
use App\Model\Shop\Entity\ShopType\ShopTypeRepository;

class Handler
{
    private ShopTypeRepository $shopTypeRepository;
    private Flusher $flusher;

    public function __construct(ShopTypeRepository $shopTypeRepository, Flusher $flusher)
    {
        $this->shopTypeRepository = $shopTypeRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $shopType = new ShopType(
            $command->name
        );

        $this->shopTypeRepository->add($shopType);

        $this->flusher->flush();
    }
}
