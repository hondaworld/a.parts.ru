<?php

namespace App\Model\Card\UseCase\Category\Edit;

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
        $zapCategory = $this->repository->get($command->zapCategoryID);

        $zapCategory->update($command->name);

        $this->flusher->flush();
    }
}
