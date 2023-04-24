<?php

namespace App\Model\Shop\UseCase\ShopGtd\Edit;

use App\Model\Flusher;
use App\Model\Shop\Entity\Gtd\Gtd;
use App\Model\Shop\Entity\Gtd\ShopGtdRepository;

class Handler
{
    private $flusher;
    private $repository;

    public function __construct(ShopGtdRepository $repository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->repository = $repository;
    }

    public function handle(Command $command): void
    {
        $shopGtd = $this->repository->get($command->shop_gtdID);

        $name = new Gtd($command->name);

        if ($this->repository->hasByGtd($name, $command->shop_gtdID)) {
            throw new \DomainException('Такой ГТД уже есть');
        }

        $shopGtd->update($name);

        $this->flusher->flush();
    }
}
