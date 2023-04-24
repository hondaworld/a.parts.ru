<?php

namespace App\Model\Firm\UseCase\Firm\Others;

use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Flusher;

class Handler
{
    private $firmRepository;
    private $flusher;

    public function __construct(FirmRepository $firmRepository, Flusher $flusher)
    {
        $this->firmRepository = $firmRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $firm = $this->firmRepository->get($command->firmID);

        $firm->updateOthers($command->first_schet, $command->first_nakladnaya, $command->first_schetfak);

        $this->flusher->flush();
    }
}
