<?php

namespace App\Model\Detail\UseCase\PartsPrice\Weight;

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
    private WeightRepository $weightRepository;

    public function __construct(
        WeightRepository $repository,
        CreaterRepository $createrRepository,
        WeightFetcher $weightFetcher,
        WeightRepository $weightRepository,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
        $this->weightFetcher = $weightFetcher;
        $this->weightRepository = $weightRepository;
    }

    public function handle(Command $command): void
    {
        $number = new DetailNumber($command->number);
        $creater = $this->createrRepository->get($command->createrID);

        if ($weight = $this->weightRepository->findByNumberAndCreater($number, $creater)) {

            $weight->update(
                $command->weight,
                $command->weightIsReal
            );
        } else {

            $weight = new Weight(
                $number,
                $creater,
                $command->weight,
                $command->weightIsReal
            );

            $this->repository->add($weight);
        }

        $this->flusher->flush();
    }
}
