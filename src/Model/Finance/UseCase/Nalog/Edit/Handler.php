<?php

namespace App\Model\Finance\UseCase\Nalog\Edit;

use App\Model\Finance\Entity\Nalog\NalogRepository;
use App\Model\Flusher;

class Handler
{
    private $nalogs;
    private $flusher;

    public function __construct(NalogRepository $nalogs, Flusher $flusher)
    {
        $this->nalogs = $nalogs;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $nalog = $this->nalogs->get($command->nalogID);

        $nalog->update($command->name);

        $this->flusher->flush();
    }
}
