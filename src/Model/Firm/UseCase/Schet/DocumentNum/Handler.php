<?php

namespace App\Model\Firm\UseCase\Schet\DocumentNum;

use App\Model\Firm\Entity\Schet\SchetRepository;
use App\Model\Flusher;

class Handler
{
    private Flusher $flusher;
    private SchetRepository $schetRepository;

    public function __construct(
        SchetRepository           $schetRepository,
        Flusher                   $flusher
    )
    {
        $this->flusher = $flusher;
        $this->schetRepository = $schetRepository;
    }

    public function handle(Command $command): void
    {
        $schet = $this->schetRepository->get($command->schetID);

        $schet->updateDocumentDate($command->dateofadded, $command->comment);
        $schet->getDocument()->updatePrefixes($command->document_prefix, $command->document_sufix);

        $this->flusher->flush();
    }
}
