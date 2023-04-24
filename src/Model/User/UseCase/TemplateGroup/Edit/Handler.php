<?php

namespace App\Model\User\UseCase\TemplateGroup\Edit;

use App\Model\Flusher;
use App\Model\User\Entity\TemplateGroup\TemplateGroupRepository;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(TemplateGroupRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $templateGroup = $this->repository->get($command->templateGroupID);

        $templateGroup->update($command->name);

        $this->flusher->flush();
    }
}
