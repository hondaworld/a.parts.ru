<?php

namespace App\Model\Firm\UseCase\OrgJob\Create;

use App\Model\Firm\Entity\OrgJob\OrgJob;
use App\Model\Firm\Entity\OrgJob\OrgJobRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(OrgJobRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        if ($command->isMain) {
            $this->repository->updateMain();
        }

        $orgJob = new OrgJob($command->name, $command->isMain);

        $this->repository->add($orgJob);

        $this->flusher->flush();
    }
}
