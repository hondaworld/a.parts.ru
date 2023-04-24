<?php

namespace App\Model\Auto\UseCase\Auto\Create;

use App\Model\Auto\Entity\Auto\Auto;
use App\Model\Auto\Entity\Auto\AutoNumber;
use App\Model\Auto\Entity\Auto\AutoRepository;
use App\Model\Auto\Entity\Auto\Vin;
use App\Model\Auto\Entity\Model\AutoModelRepository;
use App\Model\Flusher;
use App\Model\User\Entity\User\User;

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

    public function handle(Command $command, User $user = null): void
    {
        $vin = new Vin($command->vin);
        $number = new AutoNumber($command->number);


//        if ($this->repository->hasByVin($vin)) {
//            throw new \DomainException('Автомобиль с таким VIN уже есть.');
//        }

        $auto = new Auto($this->autoModelRepository->get($command->auto_modelID), $vin, $number, $command->year);

        if ($user) {
            $auto->assignUser($user);
        }

        $this->repository->add($auto);

        $this->flusher->flush();
    }
}
