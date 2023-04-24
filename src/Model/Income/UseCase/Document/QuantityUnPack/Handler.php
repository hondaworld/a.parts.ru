<?php

namespace App\Model\Income\UseCase\Document\QuantityUnPack;

use App\Model\Flusher;
use App\Model\Income\Entity\Income\Income;

class Handler
{
    private $flusher;

    public function __construct(
        Flusher $flusher
    )
    {
        $this->flusher = $flusher;
    }

    public function handle(Command $command, Income $income): void
    {
        $income->addQuantityUnPack($command->quantityUnPack);
        $this->flusher->flush();
    }
}
