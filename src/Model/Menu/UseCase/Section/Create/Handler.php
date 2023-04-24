<?php

namespace App\Model\Menu\UseCase\Section\Create;

use App\Model\Flusher;
use App\Model\Menu\Entity\Action\MenuAction;
use App\Model\Menu\Entity\Section\MenuSection;
use App\Model\Menu\Entity\Section\MenuSectionRepository;
use App\ReadModel\Menu\MenuActionFetcher;
use Doctrine\Common\Collections\ArrayCollection;

class Handler
{
    private $sections;
    private $flusher;

    public function __construct(MenuSectionRepository $sections, Flusher $flusher)
    {
        $this->sections = $sections;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $menuSection = new MenuSection($command->group, $command->parent_id, $command->name, $command->icon, $command->url, $command->entity, $command->pattern, $this->sections->getNextSort($command->group, $command->parent_id));

        if (count($command->actions) > 0) {
            foreach ($command->actions as $action) {
                $menuSection->attachAction(MenuActionFetcher::STANDART_ACTIONS[$action]['name'], MenuActionFetcher::STANDART_ACTIONS[$action]['label'], MenuActionFetcher::STANDART_ACTIONS[$action]['icon']);
            }
        }

        $this->sections->add($menuSection);

        $this->flusher->flush();
    }
}
