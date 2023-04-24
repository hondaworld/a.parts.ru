<?php

namespace App\Model\Auto\UseCase\MotoModel\DescriptionTuning;

use App\Model\Auto\Entity\Model\AutoModelRepository;
use App\Model\Auto\Entity\MotoModel\MotoModelRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(MotoModelRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $motoModel = $this->repository->get($command->moto_modelID);

        $motoModel->getDescription()->updateTuning($command->tuning);

        $this->flusher->flush();
    }
}
