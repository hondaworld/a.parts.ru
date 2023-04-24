<?php

namespace App\Model\Auto\UseCase\WorkPeriod\Copy;

use App\Model\Auto\Entity\Modification\AutoModificationRepository;
use App\Model\Flusher;
use App\Model\Work\Entity\Period\WorkPeriod;
use App\Model\Work\Entity\Period\WorkPeriodRepository;

class Handler
{
    private WorkPeriodRepository $repository;
    private Flusher $flusher;
    private AutoModificationRepository $autoModificationRepository;

    public function __construct(AutoModificationRepository $autoModificationRepository, WorkPeriodRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->autoModificationRepository = $autoModificationRepository;
    }

    public function handle(Command $command): void
    {
        $autoModification = $this->autoModificationRepository->get($command->auto_modificationID);
        $copyAutoModification = $this->autoModificationRepository->get($command->copy_auto_modificationID);

        foreach ($copyAutoModification->getWorkPeriods() as $workPeriodCopy) {
            $workPeriod = new WorkPeriod($autoModification, $workPeriodCopy->getName(), $workPeriodCopy->getNorma(), $workPeriodCopy->getNumber());
            $this->repository->add($workPeriod);

            foreach ($workPeriodCopy->getGroups() as $workGroup) {
                dump($workGroup);
                $workPeriod->assignGroup($workGroup);
            }

            foreach ($workPeriodCopy->getGroupsDop() as $workGroup) {
                $workPeriod->assignGroupDop($workGroup);
            }

            foreach ($workPeriodCopy->getGroupsRec() as $workGroup) {
                $workPeriod->assignGroupRec($workGroup);
            }
        }

        $this->flusher->flush();
    }
}
