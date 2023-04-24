<?php

namespace App\Model\Contact\UseCase\TownType\Create;

use App\Model\Contact\Entity\TownType\TownType;
use App\Model\Contact\Entity\TownType\TownTypeRepository;
use App\Model\Flusher;

class Handler
{
    private $types;
    private $flusher;

    public function __construct(TownTypeRepository $types, Flusher $flusher)
    {
        $this->types = $types;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $type = new TownType($command->name_short, $command->name);

        $this->types->add($type);

        $this->flusher->flush();
    }
}
