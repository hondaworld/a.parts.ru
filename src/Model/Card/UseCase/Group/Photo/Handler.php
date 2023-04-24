<?php

namespace App\Model\Card\UseCase\Group\Photo;

use App\Model\Card\Entity\Group\ZapGroupRepository;
use App\Model\Flusher;

class Handler
{
    private ZapGroupRepository $zapGroupRepository;
    private Flusher $flusher;

    public function __construct(ZapGroupRepository $zapGroupRepository, Flusher $flusher)
    {
        $this->zapGroupRepository = $zapGroupRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $zapGroup = $this->zapGroupRepository->get($command->zapGroupID);

        $zapGroup->updatePhoto($command->photo);

        $this->flusher->flush();
    }
}
