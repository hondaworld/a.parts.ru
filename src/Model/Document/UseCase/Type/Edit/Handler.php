<?php

namespace App\Model\Document\UseCase\Type\Edit;

use App\Model\Document\Entity\Type\DocumentTypeRepository;
use App\Model\Flusher;

class Handler
{
    private $flusher;
    private $repository;

    public function __construct(DocumentTypeRepository $repository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->repository = $repository;
    }

    public function handle(Command $command): void
    {
        $type = $this->repository->get($command->doc_typeID);

        $type->update($command->name_short, $command->name_short, $command->path);

        $this->flusher->flush();
    }
}
