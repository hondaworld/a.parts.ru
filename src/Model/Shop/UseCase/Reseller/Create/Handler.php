<?php

namespace App\Model\Shop\UseCase\Reseller\Create;

use App\Model\Flusher;
use App\Model\Shop\Entity\Reseller\Reseller;
use App\Model\Shop\Entity\Reseller\ResellerRepository;

class Handler
{
    private ResellerRepository $repository;
    private Flusher $flusher;

    public function __construct(ResellerRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $reseller = new Reseller(
            $command->name
        );

        $this->repository->add($reseller);

        $this->flusher->flush();
    }
}
