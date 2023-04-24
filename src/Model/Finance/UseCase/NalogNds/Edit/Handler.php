<?php

namespace App\Model\Finance\UseCase\NalogNds\Edit;

use App\Model\Finance\Entity\NalogNds\NalogNdsRepository;
use App\Model\Flusher;

class Handler
{
    private $nalogsNds;
    private $flusher;

    public function __construct(NalogNdsRepository $nalogsNds, Flusher $flusher)
    {
        $this->nalogsNds = $nalogsNds;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $nalogNds = $this->nalogsNds->get($command->nalogNdsID);

        $nalogNds->update($command->dateofadded, $command->nds);

        $this->flusher->flush();
    }
}
