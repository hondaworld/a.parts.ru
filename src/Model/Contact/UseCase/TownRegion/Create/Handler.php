<?php

namespace App\Model\Contact\UseCase\TownRegion\Create;

use App\Model\Contact\Entity\TownRegion\TownRegion;
use App\Model\Contact\Entity\TownRegion\TownRegionRepository;
use App\Model\Flusher;

class Handler
{
    private $regions;
    private $flusher;

    public function __construct(TownRegionRepository $regions, Flusher $flusher)
    {
        $this->regions = $regions;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $region = new TownRegion($command->country, $command->name, $command->daysFromMoscow);

        $this->regions->add($region);

        $this->flusher->flush();
    }
}
