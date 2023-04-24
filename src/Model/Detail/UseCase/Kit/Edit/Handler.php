<?php

namespace App\Model\Detail\UseCase\Kit\Edit;

use App\Model\Detail\Entity\Kit\ZapCardKitRepository;
use App\Model\Flusher;

class Handler
{
    private $zapCardKitRepository;
    private $flusher;

    public function __construct(ZapCardKitRepository $zapCardKitRepository, Flusher $flusher)
    {
        $this->zapCardKitRepository = $zapCardKitRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $zapCardKit = $this->zapCardKitRepository->get($command->id);
        $zapCardKit->update($command->name);
        $this->flusher->flush();
    }
}
