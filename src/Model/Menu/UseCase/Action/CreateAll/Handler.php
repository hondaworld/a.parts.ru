<?php

namespace App\Model\Menu\UseCase\Action\CreateAll;

use App\Model\Flusher;
use App\Model\Menu\Entity\Action\MenuAction;
use App\Model\Menu\Entity\Action\MenuActionRepository;
use App\Model\Menu\Entity\Section\MenuSection;
use App\Model\Menu\Entity\Section\MenuSectionRepository;
use App\ReadModel\Menu\MenuActionFetcher;
use Doctrine\Common\Collections\ArrayCollection;

class Handler
{
    private $actions;
    private $flusher;

    public function __construct(MenuActionRepository $actions, Flusher $flusher)
    {
        $this->actions = $actions;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {

        if (count($command->actions) > 0) {
            foreach ($command->actions as $action) {
                $command->section->attachAction($command->newActions[$action]['name'], $command->newActions[$action]['label'], $command->newActions[$action]['icon']);
            }
        }

        $this->flusher->flush();
    }
}
