<?php

namespace App\Model\Work\UseCase\Category\Create;

use App\Model\Flusher;
use App\Model\Work\Entity\Category\WorkCategory;
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
        $workCategory = new WorkCategory($command->name, $this->workCategoryRepository->getNextSort());
        $this->workCategoryRepository->add($workCategory);
        $this->flusher->flush();
    }
}
