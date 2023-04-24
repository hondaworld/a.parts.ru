<?php

namespace App\Model\Detail\UseCase\KitNumber\Edit;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\KitNumber\ZapCardKitNumberRepository;
use App\Model\Flusher;

class Handler
{
    private $flusher;
    private $zapCardKitNumberRepository;

    public function __construct(ZapCardKitNumberRepository $zapCardKitNumberRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->zapCardKitNumberRepository = $zapCardKitNumberRepository;
    }

    public function handle(Command $command): void
    {
        $kitNumber = $this->zapCardKitNumberRepository->get($command->id);

        $number = new DetailNumber($command->number);
        if ($this->zapCardKitNumberRepository->hasByNumber($kitNumber->getKit(), $number, $kitNumber->getId())) {
            throw new \DomainException('Деталь с номером ' . $number->getValue() . ' уже добавлена');
        }

        $kitNumber->update(
            $number,
            $command->quantity
        );

        $this->flusher->flush();
    }
}
