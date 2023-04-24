<?php

namespace App\Model\Contact\UseCase\TownType\Edit;

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
        $type = $this->types->get($command->id);
        $type->update($command->name_short, $command->name);
        $this->flusher->flush();
    }
}
