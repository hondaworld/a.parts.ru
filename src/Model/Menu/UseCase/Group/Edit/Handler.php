<?php

namespace App\Model\Menu\UseCase\Group\Edit;

use App\Model\Flusher;
use App\Model\Menu\Entity\Group\MenuGroupRepository;

class Handler
{
    private $groups;
    private $flusher;

    public function __construct(MenuGroupRepository $groups, Flusher $flusher)
    {
        $this->groups = $groups;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $menuGroup = $this->groups->get($command->id);
        $menuGroup->update($command->name, $command->icon);
        $this->flusher->flush();
    }
}
