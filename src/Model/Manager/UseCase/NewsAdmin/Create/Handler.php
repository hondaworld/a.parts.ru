<?php

namespace App\Model\Manager\UseCase\NewsAdmin\Create;

use App\Model\Flusher;
use App\Model\Manager\Entity\NewsAdmin\NewsAdmin;
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
        $newsAdmin = new NewsAdmin(
            $command->name,
            $command->description ?: '',
            $command->type
        );

        $this->newsAdminRepository->add($newsAdmin);

        $this->flusher->flush();
    }
}
