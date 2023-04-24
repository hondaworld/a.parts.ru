<?php

namespace App\Model\Card\UseCase\Group\Create;

use App\Model\Card\Entity\Group\ZapGroup;
use App\Model\Card\Entity\Group\ZapGroupRepository;
use App\Model\Flusher;

class Handler
{
    private ZapGroupRepository $repository;
    private Flusher $flusher;

    public function __construct(ZapGroupRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $zapGroup = new ZapGroup($command->name, $command->zapCategory);

        $this->repository->add($zapGroup);

        $this->flusher->flush();
    }
}
