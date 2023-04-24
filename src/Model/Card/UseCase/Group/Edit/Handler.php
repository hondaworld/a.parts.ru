<?php

namespace App\Model\Card\UseCase\Group\Edit;

use App\Model\Card\Entity\Category\ZapCategoryRepository;
use App\Model\Card\Entity\Group\ZapGroupRepository;
use App\Model\Flusher;

class Handler
{
    private ZapCategoryRepository $zapCategoryRepository;
    private ZapGroupRepository $repository;
    private Flusher $flusher;

    public function __construct(ZapGroupRepository $repository, ZapCategoryRepository $zapCategoryRepository, Flusher $flusher)
    {
        $this->zapCategoryRepository = $zapCategoryRepository;
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $zapCategory = $this->repository->get($command->zapGroupID);

        $zapCategory->update($command->name, $this->zapCategoryRepository->get($command->zapCategoryID));

        $this->flusher->flush();
    }
}
