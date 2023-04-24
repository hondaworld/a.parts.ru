<?php

namespace App\Model\Menu\UseCase\Group\Create;

use App\Model\Flusher;
use App\Model\Menu\Entity\Group\MenuGroup;
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
        $menuGroup = new MenuGroup($command->name, $command->icon, $this->groups->getNextSort());

        $this->groups->add($menuGroup);

        $this->flusher->flush();
    }
}
