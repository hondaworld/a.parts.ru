<?php

namespace App\Model\User\UseCase\Discount\Create;

use App\Model\Flusher;
use App\Model\Shop\Entity\Discount\Discount;
use App\Model\Shop\Entity\Discount\DiscountRepository;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(DiscountRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $discount = new Discount($command->summ, $command->discount_spare, $command->discount_service);

        $this->repository->add($discount);

        $this->flusher->flush();
    }
}
