<?php

namespace App\Model\Contact\UseCase\Country\Create;

use App\Model\Contact\Entity\Country\Country;
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
        $country = new Country($command->name, $command->code);

        $this->countries->add($country);

        $this->flusher->flush();
    }
}
