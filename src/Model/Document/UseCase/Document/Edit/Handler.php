<?php

namespace App\Model\Document\UseCase\Document\Edit;

use App\Model\Document\Entity\Document\DocumentRepository;
use App\Model\Document\Entity\Identification\DocumentIdentificationRepository;
use App\Model\Flusher;

class Handler
{
    private $documents;
    private $flusher;
    private $identifications;

    public function __construct(DocumentRepository $documents, DocumentIdentificationRepository $identifications, Flusher $flusher)
    {
        $this->documents = $documents;
        $this->flusher = $flusher;
        $this->identifications = $identifications;
    }

    public function handle(Command $command): void
    {
        $document = $this->documents->get($command->documentID);

        if ($command->manager) {
            $command->isMain = $command->manager->checkIsMainDocument($command->isMain);
        }

        if ($command->user) {
            $command->isMain = $command->user->checkIsMainDocument($command->isMain);
        }

        if ($command->firm) {
            $command->isMain = $command->firm->checkIsMainDocument($command->isMain);
        }

        $document->update(
            $this->identifications->get($command->doc_identID), $command->serial, $command->number, $command->organization, $command->dateofdoc, $command->description, $command->isMain
        );

        $this->flusher->flush();
    }
}
