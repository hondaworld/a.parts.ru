<?php

namespace App\Model\Manager\UseCase\Group\Create;

use App\Model\Flusher;
use App\Model\Manager\Entity\Group\ManagerGroup;
use App\Model\Manager\Entity\Group\ManagerGroupRepository;

class Handler
{
    private $groups;
    private $flusher;

    public function __construct(ManagerGroupRepository $managers, Flusher $flusher)
    {
        $this->groups = $managers;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $managerGroup = new ManagerGroup($command->name);

        $this->groups->add($managerGroup);

        $this->flusher->flush();
    }
}
