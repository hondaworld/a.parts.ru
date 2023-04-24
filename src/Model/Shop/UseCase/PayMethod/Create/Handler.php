<?php

namespace App\Model\Shop\UseCase\PayMethod\Create;

use App\Model\Flusher;
use App\Model\Shop\Entity\PayMethod\PayMethod;
use App\Model\Shop\Entity\PayMethod\PayMethodRepository;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(PayMethodRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        if ($command->isMain) {
            $this->repository->updateMain();
        }

        $payMethod = new PayMethod(
            $command->val,
            $command->description,
            $command->isMain,
            $this->repository->getNextSort()
        );

        $this->repository->add($payMethod);

        $this->flusher->flush();
    }
}
