<?php

namespace App\Model\Detail\UseCase\Kit\Copy;

use App\Model\Detail\Entity\Kit\ZapCardKit;
use App\Model\Detail\Entity\Kit\ZapCardKitRepository;
use App\Model\Detail\Entity\KitNumber\ZapCardKitNumber;
use App\Model\Detail\Entity\KitNumber\ZapCardKitNumberRepository;
use App\Model\Flusher;

class Handler
{
    private $zapCardKitRepository;
    private $flusher;
    private ZapCardKitNumberRepository $zapCardKitNumberRepository;

    public function __construct(ZapCardKitRepository $zapCardKitRepository, ZapCardKitNumberRepository $zapCardKitNumberRepository, Flusher $flusher)
    {
        $this->zapCardKitRepository = $zapCardKitRepository;
        $this->flusher = $flusher;
        $this->zapCardKitNumberRepository = $zapCardKitNumberRepository;
    }

    public function handle(Command $command): void
    {
        $copy = $this->zapCardKitRepository->get($command->copyID);

        $zapCardKit = new ZapCardKit($copy->getAutoModel(), $command->name, $this->zapCardKitRepository->getNextSort($copy->getAutoModel()));
        $this->zapCardKitRepository->add($zapCardKit);

        foreach ($copy->getNumbers() as $item) {
            $zapCardKitNumber = new ZapCardKitNumber(
                $zapCardKit,
                $item->getNumber(),
                $item->getQuantity(),
                $item->getSort()
            );
            $this->zapCardKitNumberRepository->add($zapCardKitNumber);
        }

        $this->flusher->flush();
    }
}
