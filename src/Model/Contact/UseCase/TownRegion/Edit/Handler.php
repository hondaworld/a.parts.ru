<?php

namespace App\Model\Contact\UseCase\TownRegion\Edit;

use App\Model\Contact\Entity\Country\CountryRepository;
use App\Model\Contact\Entity\TownRegion\TownRegionRepository;
use App\Model\Flusher;

class Handler
{
    private $regions;
    private $flusher;
    private $countries;

    public function __construct(TownRegionRepository $regions, CountryRepository $countries, Flusher $flusher)
    {
        $this->regions = $regions;
        $this->flusher = $flusher;
        $this->countries = $countries;
    }

    public function handle(Command $command): void
    {
        $region = $this->regions->get($command->regionID);

        $region->update($this->countries->get($command->countryID), $command->name, $command->daysFromMoscow);

        $this->flusher->flush();
    }
}
