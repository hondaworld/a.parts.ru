<?php

namespace App\Model\Work\UseCase\Category\Edit;

use App\Model\Flusher;
use App\Model\Work\Entity\Category\WorkCategoryRepository;

class Handler
{
    private $workCategoryRepository;
    private $flusher;

    public function __construct(WorkCategoryRepository $workCategoryRepository, Flusher $flusher)
    {
        $this->workCategoryRepository = $workCategoryRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $workCategory = $this->workCategoryRepository->get($command->id);
        $workCategory->update($command->name);
        $this->flusher->flush();
    }
}
