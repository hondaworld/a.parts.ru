<?php

namespace App\Model\Work\UseCase\Group\Edit;

use App\Model\Flusher;
use App\Model\Work\Entity\Category\WorkCategoryRepository;
use App\Model\Work\Entity\Group\WorkGroupRepository;

class Handler
{
    private $workCategoryRepository;
    private $repository;
    private $flusher;

    public function __construct(WorkGroupRepository $repository, WorkCategoryRepository $workCategoryRepository, Flusher $flusher)
    {
        $this->workCategoryRepository = $workCategoryRepository;
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $workGroup = $this->repository->get($command->workGroupID);
        $workGroup->update($this->workCategoryRepository->get($command->workCategoryID), $command->name, $command->norma, $command->isTO, $command->sort);
        $this->flusher->flush();
    }
}
