<?php

namespace App\Model\Auto\UseCase\Moto\Create;

use App\Model\Auto\Entity\Auto\Auto;
use App\Model\Auto\Entity\Auto\AutoNumber;
use App\Model\Auto\Entity\Auto\AutoRepository;
use App\Model\Auto\Entity\Auto\Vin;
use App\Model\Auto\Entity\Model\AutoModelRepository;
use App\Model\Auto\Entity\Moto\Moto;
use App\Model\Auto\Entity\Moto\MotoRepository;
use App\Model\Auto\Entity\MotoModel\MotoModelRepository;
use App\Model\Flusher;
use App\Model\User\Entity\User\User;

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

    public function handle(Command $command, User $user = null): void
    {
        $vin = new Vin($command->vin);
        $number = new AutoNumber($command->number);


        if ($this->repository->hasByVin($vin)) {
            throw new \DomainException('Мотоцикл с таким VIN уже есть.');
        }

        $moto = new Moto($this->motoModelRepository->get($command->moto_modelID), $vin, $number, $command->year);

        if ($user) {
            $moto->assignUser($user);
        }

        $this->repository->add($moto);

        $this->flusher->flush();
    }
}
