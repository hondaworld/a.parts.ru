<?php

namespace App\Model\Firm\UseCase\OrgGroup\Edit;

use App\Model\Firm\Entity\OrgGroup\OrgGroupRepository;
use App\Model\Flusher;

class Handler
{
    private $flusher;
    private $repository;

    public function __construct(OrgGroupRepository $repository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->repository = $repository;
    }

    public function handle(Command $command): void
    {
        $orgGroup = $this->repository->get($command->org_groupID);

        if ($command->isMain) {
            $this->repository->updateMain();
        }

        $orgGroup->update($command->name, $command->isMain);

        $this->flusher->flush();
    }
}
