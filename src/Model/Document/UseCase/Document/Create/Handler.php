<?php

namespace App\Model\Document\UseCase\Document\Create;

use App\Model\Document\Entity\Document\Document;
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
        $object = null;

        if ($command->manager) {
            $command->isMain = $command->manager->checkIsMainDocument($command->isMain);
            $object = $command->manager;
        }

        if ($command->user) {
            $command->isMain = $command->user->checkIsMainDocument($command->isMain);
            $object = $command->user;
        }

        if ($command->firm) {
            $command->isMain = $command->firm->checkIsMainDocument($command->isMain);
            $object = $command->firm;
        }

        $document = new Document(
            $object, $this->identifications->get($command->doc_identID), $command->serial, $command->number, $command->organization, $command->dateofdoc, $command->description, $command->isMain
        );

        $object->assignDocument($document);

        $this->flusher->flush();
    }
}
