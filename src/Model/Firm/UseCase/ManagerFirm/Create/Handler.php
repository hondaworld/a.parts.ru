<?php

namespace App\Model\Firm\UseCase\ManagerFirm\Create;

use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Firm\Entity\ManagerFirm\ManagerFirm;
use App\Model\Firm\Entity\ManagerFirm\ManagerFirmRepository;
use App\Model\Firm\Entity\OrgGroup\OrgGroupRepository;
use App\Model\Firm\Entity\OrgJob\OrgJobRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;

class Handler
{
    private $repository;
    private $flusher;
    private ManagerRepository $managerRepository;
    private OrgGroupRepository $orgGroupRepository;
    private OrgJobRepository $orgJobRepository;
    private FirmRepository $firmRepository;

    public function __construct(
        ManagerFirmRepository $repository,
        FirmRepository $firmRepository,
        ManagerRepository $managerRepository,
        OrgGroupRepository $orgGroupRepository,
        OrgJobRepository $orgJobRepository,
        Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->managerRepository = $managerRepository;
        $this->orgGroupRepository = $orgGroupRepository;
        $this->orgJobRepository = $orgJobRepository;
        $this->firmRepository = $firmRepository;
    }

    public function handle(Command $command): void
    {
        $managerFirm = new ManagerFirm(
            $command->manager ?: $this->managerRepository->get($command->managerID),
            $command->firm ?: $this->firmRepository->get($command->firmID),
            $this->orgGroupRepository->get($command->org_groupID),
            $this->orgJobRepository->get($command->org_jobID),
            $command->dateofadded,
            $command->dateofclosed
        );

        $this->repository->add($managerFirm);

        $this->flusher->flush();
    }
}
