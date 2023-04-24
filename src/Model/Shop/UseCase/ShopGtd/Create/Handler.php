<?php

namespace App\Model\Shop\UseCase\ShopGtd\Create;

use App\Model\Flusher;
use App\Model\Shop\Entity\Gtd\Gtd;
use App\Model\Shop\Entity\Gtd\ShopGtd;
use App\Model\Shop\Entity\Gtd\ShopGtdRepository;

class Handler
{
    private ShopGtdRepository $repository;
    private Flusher $flusher;

    public function __construct(ShopGtdRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $name = new Gtd($command->name);

        if ($this->repository->hasByGtd($name)) {
            throw new \DomainException('Такой ГТД уже есть');
        }

        $shopGtd = new ShopGtd($name);
        $this->repository->add($shopGtd);
        $this->flusher->flush();
    }
}
