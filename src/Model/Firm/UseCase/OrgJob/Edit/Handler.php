<?php

namespace App\Model\Firm\UseCase\OrgJob\Edit;

use App\Model\Firm\Entity\OrgJob\OrgJobRepository;
use App\Model\Flusher;

class Handler
{
    private $flusher;
    private $repository;

    public function __construct(OrgJobRepository $repository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->repository = $repository;
    }

    public function handle(Command $command): void
    {
        $orgJob = $this->repository->get($command->org_jobID);

        if ($command->isMain) {
            $this->repository->updateMain();
        }

        $orgJob->update($command->name, $command->isMain);

        $this->flusher->flush();
    }
}
