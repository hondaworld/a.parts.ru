<?php

namespace App\Model\Document\UseCase\Type\Create;

use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Document\Entity\Type\DocumentTypeRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(DocumentTypeRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $type = new DocumentType($command->name_short, $command->name, $command->path);

        $this->repository->add($type);

        $this->flusher->flush();
    }
}
