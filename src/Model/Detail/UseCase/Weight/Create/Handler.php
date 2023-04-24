<?php

namespace App\Model\Detail\UseCase\Weight\Create;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Detail\Entity\Weight\Weight;
use App\Model\Detail\Entity\Weight\WeightRepository;
use App\Model\Flusher;
use App\ReadModel\Detail\WeightFetcher;

class Handler
{
    private $repository;
    private $flusher;
    private CreaterRepository $createrRepository;
    private WeightFetcher $weightFetcher;

    public function __construct(
        WeightRepository $repository,
        CreaterRepository $createrRepository,
        WeightFetcher $weightFetcher,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
        $this->weightFetcher = $weightFetcher;
    }

    public function handle(Command $command): void
    {
        $number = new DetailNumber($command->number);
        $creater = $this->createrRepository->get($command->createrID);

        if ($this->weightFetcher->hasWeight($number->getValue(), $command->createrID)) {
            throw new \DomainException('Такой вес уже есть.');
        }

        $weight = new Weight(
            $number,
            $creater,
            $command->weight,
            $command->weightIsReal
        );

        $this->repository->add($weight);

        $this->flusher->flush();
    }
}
