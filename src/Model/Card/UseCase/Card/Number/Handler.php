<?php

namespace App\Model\Card\UseCase\Card\Number;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;
    private CreaterRepository $createrRepository;

    public function __construct(
        ZapCardRepository $repository,
        CreaterRepository $createrRepository,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
    }

    public function handle(Command $command): void
    {
        $number = new DetailNumber($command->number);
        if ($this->repository->hasByNumber($number, $this->createrRepository->get($command->createrID), $command->zapCardID)) {
            throw new \DomainException('Такой номер уже есть');
        }

        $zapCard = $this->repository->get($command->zapCardID);

        $zapCard->updateNumber(
            $number,
            $this->createrRepository->get($command->createrID)
        );

        $this->flusher->flush();
    }
}
