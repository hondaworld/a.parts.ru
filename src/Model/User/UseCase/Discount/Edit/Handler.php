<?php

namespace App\Model\User\UseCase\Discount\Edit;

use App\Model\Flusher;
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
        $discount = $this->repository->get($command->discountID);

        $discount->update($command->summ, $command->discount_spare, $command->discount_service);

        $this->flusher->flush();
    }
}
