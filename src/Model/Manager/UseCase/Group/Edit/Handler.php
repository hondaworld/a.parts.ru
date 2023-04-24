<?php

namespace App\Model\Manager\UseCase\Group\Edit;

use App\Model\Flusher;
use App\Model\Manager\Entity\Group\ManagerGroupRepository;
use App\Model\Menu\Entity\Action\MenuAction;
use App\Model\Menu\Entity\Action\MenuActionRepository;

class Handler
{
    private $groups;
    private $flusher;
    /**
     * @var MenuActionRepository
     */
    private MenuActionRepository $actions;

    public function __construct(ManagerGroupRepository $groups, MenuActionRepository $actions, Flusher $flusher)
    {
        $this->groups = $groups;
        $this->flusher = $flusher;
        $this->actions = $actions;
    }

    public function handle(Command $command): void
    {
        $managerGroup = $this->groups->get($command->managerGroupID);

        $actions = array_map(function (int $id): MenuAction {
            return $this->actions->get($id);
        }, $command->actions);

        $managerGroup->update($command->name, $actions);
        $this->flusher->flush();
    }
}
