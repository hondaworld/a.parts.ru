<?php

namespace App\Model\Card\UseCase\Card\Weight;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Detail\Entity\Weight\Weight;
use App\Model\Detail\Entity\Weight\WeightRepository;
use App\Model\Flusher;

class Handler
{
    private ZapCardRepository $repository;
    private Flusher $flusher;
    private WeightRepository $weightRepository;

    public function __construct(
        ZapCardRepository $repository,
        WeightRepository $weightRepository,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->weightRepository = $weightRepository;
    }

    public function handle(Command $command, ?Weight $weight): void
    {
        $zapCard = $this->repository->get($command->zapCardID);

        if ($weight) {
            $weight->update($command->weight, $command->weightIsReal);
        } else {
            $weight = new Weight($zapCard->getNumber(), $zapCard->getCreater(), $command->weight, $command->weightIsReal);
            $this->weightRepository->add($weight);
        }

        $this->flusher->flush();
    }
}
