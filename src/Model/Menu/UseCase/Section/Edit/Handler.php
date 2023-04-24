<?php

namespace App\Model\Menu\UseCase\Section\Edit;

use App\Model\Flusher;
use App\Model\Menu\Entity\Group\MenuGroup;
use App\Model\Menu\Entity\Group\MenuGroupRepository;
use App\Model\Menu\Entity\Section\MenuSectionRepository;

class Handler
{
    /**
     * @var MenuSectionRepository
     */
    private MenuSectionRepository $sections;

    /**
     * @var Flusher
     */
    private Flusher $flusher;

    /**
     * @var MenuGroupRepository
     */
    private MenuGroupRepository $groups;

    public function __construct(MenuSectionRepository $sections, MenuGroupRepository $groups, Flusher $flusher)
    {
        $this->sections = $sections;
        $this->flusher = $flusher;
        $this->groups = $groups;
    }

    public function handle(Command $command): void
    {
        $command->group = $this->groups->get($command->parent->item['menu_group_id']);
        $command->parent_id = $command->parent->item['id'];

        $menuSection = $this->sections->get($command->id);

        $menuSection->update($command->name, $command->icon, $command->url, $command->entity, $command->pattern);

        if ($menuSection->getParentId() != $command->parent_id || $menuSection->getGroup()->getId() != $command->group->getId()) {
            $this->sections->removeSort($menuSection->getGroup(), $menuSection->getParentId(), $menuSection->getSort());
            $menuSection->changeSort($this->sections->getNextSort($command->group, $command->parent_id));

            $menuSection->changeParentId($command->parent_id);
            $menuSection->changeGroup($command->group);
            $this->getSectionChildren($command->group, $command->id);
        }

        $this->flusher->flush();
    }

    private function getSectionChildren(MenuGroup $group, int $parent_id)
    {
        $children = $this->sections->findByParentId($parent_id);
        if ($children) {
            foreach ($children as $child) {
                $child->changeGroup($group);
                $this->getSectionChildren($group, $child->getId());
            }
        }
    }
}
