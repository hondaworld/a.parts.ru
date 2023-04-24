<?php

namespace App\Model\User\UseCase\Opt\Create;

use App\Model\Flusher;
use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\Opt\OptRepository;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(OptRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $shopPayType = new Opt($command->name, $this->repository->getNextSort());

        $this->repository->add($shopPayType);

        $this->flusher->flush();
    }
}
