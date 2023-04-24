<?php

namespace App\Model\Auto\UseCase\Auto\Edit;

use App\Model\Auto\Entity\Auto\AutoNumber;
use App\Model\Auto\Entity\Auto\AutoRepository;
use App\Model\Auto\Entity\Auto\Vin;
use App\Model\Auto\Entity\Model\AutoModelRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;
    private $autoModelRepository;

    public function __construct(AutoModelRepository $autoModelRepository, AutoRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->autoModelRepository = $autoModelRepository;
    }

    public function handle(Command $command): void
    {
        $auto = $this->repository->get($command->autoID);

        $vin = new Vin($command->vin);
        $number = new AutoNumber($command->number);

//        if ($this->repository->hasByVin($vin, $command->autoID)) {
//            throw new \DomainException('Автомобиль с таким VIN уже есть.');
//        }

        $auto->update($this->autoModelRepository->get($command->auto_modelID), $vin, $number, $command->year);

        $this->flusher->flush();
    }
}
