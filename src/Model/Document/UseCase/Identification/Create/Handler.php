<?php

namespace App\Model\Document\UseCase\Identification\Create;

use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Document\Entity\Document\Document;
use App\Model\Document\Entity\Document\DocumentRepository;
use App\Model\Document\Entity\Identification\DocumentIdentification;
use App\Model\Document\Entity\Identification\DocumentIdentificationRepository;
use App\Model\Flusher;

class Handler
{
    private $identifications;
    private $flusher;

    public function __construct(DocumentIdentificationRepository $identifications, Flusher $flusher)
    {
        $this->identifications = $identifications;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $identification = new DocumentIdentification(
            $command->name
        );

        $this->identifications->add($identification);

        $this->flusher->flush();
    }
}
