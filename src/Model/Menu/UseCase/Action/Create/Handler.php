<?php

namespace App\Model\Menu\UseCase\Action\Create;

use App\Model\Flusher;
use App\Model\Menu\Entity\Action\MenuAction;
use App\Model\Menu\Entity\Action\MenuActionRepository;

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
        $menuAction = new MenuAction($command->section, $command->name, $command->label, $command->icon);

        foreach ($command->section->getActions() as $existing) {
            if ($existing->isForAction($command->name)) {
                throw new \DomainException('Такая операция уже существует');
            }
        }

        $this->actions->add($menuAction);

        $this->flusher->flush();
    }
}
