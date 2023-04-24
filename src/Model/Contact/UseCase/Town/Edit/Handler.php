<?php

namespace App\Model\Contact\UseCase\Town\Edit;

use App\Model\Contact\Entity\Town\TownRepository;
use App\Model\Contact\Entity\TownRegion\TownRegionRepository;
use App\Model\Contact\Entity\TownType\TownTypeRepository;
use App\Model\Flusher;

class Handler
{
    private $towns;
    private $regions;
    private $types;
    private $flusher;

    public function __construct(TownRepository $towns, TownRegionRepository $regions, TownTypeRepository $types, Flusher $flusher)
    {
        $this->towns = $towns;
        $this->regions = $regions;
        $this->types = $types;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $town = $this->towns->get($command->townID);

        $town->update(
            $this->regions->get($command->regionID),
            $this->types->get($command->typeID),
            $command->name,
            $command->name_short,
            $command->name_doc,
            $command->daysFromMoscow,
            $command->isFree
        );

        $this->flusher->flush();
    }
}
