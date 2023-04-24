<?php

namespace App\Model\Contact\UseCase\Country\Edit;

use App\Model\Contact\Entity\Country\CountryRepository;
use App\Model\Flusher;

class Handler
{
    private $countries;
    private $flusher;

    public function __construct(CountryRepository $countries, Flusher $flusher)
    {
        $this->countries = $countries;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $country = $this->countries->get($command->countryID);
        $country->update($command->name, $command->code);
        $this->flusher->flush();
    }
}
