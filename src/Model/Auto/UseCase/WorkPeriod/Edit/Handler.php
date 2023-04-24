<?php

namespace App\Model\Auto\UseCase\WorkPeriod\Edit;

use App\Model\Flusher;
use App\Model\Work\Entity\Group\WorkGroupRepository;
use App\Model\Work\Entity\Period\WorkPeriodRepository;

class Handler
{
    private WorkPeriodRepository $repository;
    private Flusher $flusher;
    private WorkGroupRepository $workGroupRepository;

    public function __construct(WorkPeriodRepository $repository, WorkGroupRepository $workGroupRepository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->workGroupRepository = $workGroupRepository;
    }

    public function handle(Command $command): void
    {
        $workPeriod = $this->repository->get($command->workPeriodID);

        $workPeriod->update($command->name, $command->norma);

        $workPeriod->clearGroups();
        foreach ($command->groups as $groupID) {
            $workGroup = $this->workGroupRepository->get($groupID);
            $workPeriod->assignGroup($workGroup);
        }

        $workPeriod->clearGroupsDop();
        foreach ($command->groups_dop as $groupID) {
            $workGroup = $this->workGroupRepository->get($groupID);
            $workPeriod->assignGroupDop($workGroup);
        }

        $workPeriod->clearGroupsRec();
        foreach ($command->groups_rec as $groupID) {
            $workGroup = $this->workGroupRepository->get($groupID);
            $workPeriod->assignGroupRec($workGroup);
        }

        $this->flusher->flush();
    }
}
