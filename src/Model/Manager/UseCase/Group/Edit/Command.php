<?php

namespace App\Model\Manager\UseCase\Group\Edit;

use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Menu\Entity\Action\MenuAction;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $managerGroupID;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $name;

    public $actions;

    public $actionsList;

    public function __construct(int $managerGroupID)
    {
        $this->managerGroupID = $managerGroupID;
    }

    public static function fromManagerGroup(ManagerGroup $managerGroup, array $actionsList): self
    {
        $command = new self($managerGroup->getId());
        $command->name = $managerGroup->getName();
        $command->actions = array_map(function (MenuAction $action): int {
            return $action->getId();
        }, $managerGroup->getActions());

        $command->actionsList = $actionsList;

        return $command;
    }
}
