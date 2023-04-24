<?php

namespace App\Model\Auto\UseCase\Moto\Edit;

use App\Model\Auto\Entity\Auto\AutoNumber;
use App\Model\Auto\Entity\Auto\Vin;
use App\Model\Auto\Entity\Moto\MotoRepository;
use App\Model\Auto\Entity\MotoModel\MotoModelRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;
    private $motoModelRepository;

    public function __construct(MotoModelRepository $motoModelRepository, MotoRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->motoModelRepository = $motoModelRepository;
    }

    public function handle(Command $command): void
    {
        $auto = $this->repository->get($command->motoID);

        $vin = new Vin($command->vin);
        $number = new AutoNumber($command->number);

        if ($this->repository->hasByVin($vin, $command->motoID)) {
            throw new \DomainException('Мотоцикл с таким VIN уже есть.');
        }

        $auto->update($this->motoModelRepository->get($command->moto_modelID), $vin, $number, $command->year);

        $this->flusher->flush();
    }
}
