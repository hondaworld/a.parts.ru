<?php

namespace App\Model\Card\UseCase\Category\Create;

use App\Model\Card\Entity\Category\ZapCategory;
use App\Model\Card\Entity\Category\ZapCategoryRepository;
use App\Model\Flusher;

class Handler
{
    private ZapCategoryRepository $repository;
    private Flusher $flusher;

    public function __construct(ZapCategoryRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $zapCategory = new ZapCategory($command->name, $this->repository->getNextSort());

        $this->repository->add($zapCategory);

        $this->flusher->flush();
    }
}
