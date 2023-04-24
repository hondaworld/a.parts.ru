<?php

namespace App\Model\Firm\UseCase\Schet\PayUrl;

use App\Model\Firm\Entity\Schet\SchetRepository;
use App\Model\Flusher;

class Handler
{
    private Flusher $flusher;
    private SchetRepository $schetRepository;

    public function __construct(
        SchetRepository          $schetRepository,
        Flusher                  $flusher
    )
    {
        $this->flusher = $flusher;
        $this->schetRepository = $schetRepository;
    }

    public function handle(Command $command): void
    {
        $schet = $this->schetRepository->get($command->schetID);
        $schet->updatePayUrl($command->pay_url);
        $this->flusher->flush();
    }
}
