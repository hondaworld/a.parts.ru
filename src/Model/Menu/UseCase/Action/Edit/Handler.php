<?php

namespace App\Model\Menu\UseCase\Action\Edit;

use App\Model\Flusher;
use App\Model\Menu\Entity\Action\MenuActionRepository;

class Handler
{
    /**
     * @var Flusher
     */
    private Flusher $flusher;

    /**
     * @var MenuActionRepository
     */
    private MenuActionRepository $actions;

    public function __construct(MenuActionRepository $actions, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->actions = $actions;
    }

    public function handle(Command $command): void
    {
        $menuAction = $this->actions->get($command->id);

        foreach ($command->section->getActions() as $existing) {
            if ($existing->isForAction($command->name) && $command->id <> $existing->getId()) {
                throw new \DomainException('Такая операция уже существует');
            }
        }

        $menuAction->update($command->name, $command->label, $command->icon);

        $this->flusher->flush();
    }
}
