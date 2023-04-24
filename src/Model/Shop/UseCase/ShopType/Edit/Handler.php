<?php

namespace App\Model\Shop\UseCase\ShopType\Edit;

use App\Model\Flusher;
use App\Model\Shop\Entity\ShopType\ShopTypeRepository;

class Handler
{
    private Flusher $flusher;
    private ShopTypeRepository $shopTypeRepository;

    public function __construct(ShopTypeRepository $shopTypeRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->shopTypeRepository = $shopTypeRepository;
    }

    public function handle(Command $command): void
    {
        $shopType = $this->shopTypeRepository->get($command->shop_typeID);

        $shopType->update(
            $command->name
        );

        $this->flusher->flush();
    }
}
