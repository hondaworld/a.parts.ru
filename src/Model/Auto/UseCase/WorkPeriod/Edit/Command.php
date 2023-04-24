<?php

namespace App\Model\Auto\UseCase\WorkPeriod\Edit;

use App\Model\Work\Entity\Group\WorkGroup;
use App\Model\Work\Entity\Period\WorkPeriod;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $workPeriodID;

    /**
     * @Assert\NotBlank()
     */
    public $name;

    public $norma;

    /**
     * @var array
     */
    public $groups;

    /**
     * @var array
     */
    public $groups_dop;

    /**
     * @var array
     */
    public $groups_rec;

    public function __construct(int $workPeriodID)
    {
        $this->workPeriodID = $workPeriodID;
    }

    public static function fromEntity(WorkPeriod $workPeriod): self
    {
        $command = new self($workPeriod->getId());
        $command->name = $workPeriod->getName();
        $command->norma = $workPeriod->getNorma();

        $command->groups = array_map(function (WorkGroup $workGroup): int {
            return $workGroup->getId();
        }, $workPeriod->getGroups());

        $command->groups_dop = array_map(function (WorkGroup $workGroup): int {
            return $workGroup->getId();
        }, $workPeriod->getGroupsDop());

        $command->groups_rec = array_map(function (WorkGroup $workGroup): int {
            return $workGroup->getId();
        }, $workPeriod->getGroupsRec());

        return $command;
    }
}
