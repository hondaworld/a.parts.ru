<?php

namespace App\Model\Finance\UseCase\Nalog\Create;

use App\Model\Finance\Entity\Nalog\Nalog;
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
        $nalog = new Nalog($command->name);
        $nalog->addNds($command->dateofadded, $command->nds);

        $this->nalogs->add($nalog);

        $this->flusher->flush();
    }
}
