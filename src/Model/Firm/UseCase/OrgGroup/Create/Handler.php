<?php

namespace App\Model\Firm\UseCase\OrgGroup\Create;

use App\Model\Firm\Entity\OrgGroup\OrgGroup;
use App\Model\Firm\Entity\OrgGroup\OrgGroupRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(OrgGroupRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        if ($command->isMain) {
            $this->repository->updateMain();
        }

        $orgGroup = new OrgGroup($command->name, $command->isMain);

        $this->repository->add($orgGroup);

        $this->flusher->flush();
    }
}
