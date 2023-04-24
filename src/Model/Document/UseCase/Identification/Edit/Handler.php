<?php

namespace App\Model\Document\UseCase\Identification\Edit;

use App\Model\Document\Entity\Identification\DocumentIdentificationRepository;
use App\Model\Flusher;

class Handler
{
    private $flusher;
    private $identifications;

    public function __construct(DocumentIdentificationRepository $identifications, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->identifications = $identifications;
    }

    public function handle(Command $command): void
    {
        $identification = $this->identifications->get($command->doc_identID);

        $identification->update(
            $command->name
        );

        $this->flusher->flush();
    }
}
