<?php

namespace App\Model\Detail\UseCase\KitNumber\Create;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Kit\ZapCardKit;
use App\Model\Detail\Entity\KitNumber\ZapCardKitNumber;
use App\Model\Detail\Entity\KitNumber\ZapCardKitNumberRepository;
use App\Model\Flusher;

class Handler
{
    private $zapCardKitNumberRepository;
    private $flusher;

    public function __construct(ZapCardKitNumberRepository $zapCardKitNumberRepository, Flusher $flusher)
    {
        $this->zapCardKitNumberRepository = $zapCardKitNumberRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command, ZapCardKit $zapCardKit): void
    {
        $number = new DetailNumber($command->number);
        if ($this->zapCardKitNumberRepository->hasByNumber($zapCardKit, $number)) {
            throw new \DomainException('Деталь с номером ' . $number->getValue() . ' уже добавлена');
        }

        $zapCardKitNumber = new ZapCardKitNumber(
            $zapCardKit,
            $number,
            $command->quantity,
            $this->zapCardKitNumberRepository->getNextSort($zapCardKit)
        );
        $this->zapCardKitNumberRepository->add($zapCardKitNumber);
        $this->flusher->flush();
    }
}
