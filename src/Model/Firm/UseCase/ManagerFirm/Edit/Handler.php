<?php

namespace App\Model\Firm\UseCase\ManagerFirm\Edit;

use App\Model\Firm\Entity\ManagerFirm\ManagerFirmRepository;
use App\Model\Firm\Entity\OrgGroup\OrgGroupRepository;
use App\Model\Firm\Entity\OrgJob\OrgJobRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;
    private OrgGroupRepository $orgGroupRepository;
    private OrgJobRepository $orgJobRepository;

    public function __construct(
        ManagerFirmRepository $repository,
        OrgGroupRepository $orgGroupRepository,
        OrgJobRepository $orgJobRepository,
        Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->orgGroupRepository = $orgGroupRepository;
        $this->orgJobRepository = $orgJobRepository;
    }

    public function handle(Command $command): void
    {
        $managerFirm = $this->repository->get($command->linkID);

        $managerFirm->update(
            $this->orgGroupRepository->get($command->org_groupID),
            $this->orgJobRepository->get($command->org_jobID),
            $command->dateofadded,
            $command->dateofclosed
        );

        $this->flusher->flush();
    }
}
