<?php

namespace App\Model\Detail\UseCase\Kit\Create;

use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Detail\Entity\Kit\ZapCardKit;
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

    public function handle(Command $command, AutoModel $autoModel): void
    {
        $zapCardKit = new ZapCardKit($autoModel, $command->name, $this->zapCardKitRepository->getNextSort($autoModel));
        $this->zapCardKitRepository->add($zapCardKit);
        $this->flusher->flush();
    }
}
