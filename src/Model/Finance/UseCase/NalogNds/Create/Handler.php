<?php

namespace App\Model\Finance\UseCase\NalogNds\Create;

use App\Model\Finance\Entity\NalogNds\NalogNds;
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
        $nds = new NalogNds($command->nalog, $command->dateofadded, $command->nds);

        $this->nalogsNds->add($nds);

        $this->flusher->flush();
    }
}
