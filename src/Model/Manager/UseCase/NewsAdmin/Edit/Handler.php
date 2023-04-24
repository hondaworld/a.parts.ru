<?php

namespace App\Model\Manager\UseCase\NewsAdmin\Edit;

use App\Model\Flusher;
use App\Model\Manager\Entity\NewsAdmin\NewsAdminRepository;

class Handler
{
    private NewsAdminRepository $newsAdminRepository;
    private Flusher $flusher;

    public function __construct(NewsAdminRepository $newsAdminRepository, Flusher $flusher)
    {
        $this->newsAdminRepository = $newsAdminRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $newsAdmin = $this->newsAdminRepository->get($command->newsID);

        $newsAdmin->update(
            $command->name,
            $command->description ?: '',
            $command->type,
            $command->dateofadded
        );

        $this->flusher->flush();
    }
}
