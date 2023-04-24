<?php

namespace App\Model\Auto\UseCase\MotoModel\Create;

use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Auto\Entity\MotoGroup\MotoGroupRepository;
use App\Model\Auto\Entity\MotoModel\MotoModel;
use App\Model\Auto\Entity\MotoModel\MotoModelRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;
    private MotoGroupRepository $motoGroupRepository;

    public function __construct(MotoModelRepository $repository, MotoGroupRepository $motoGroupRepository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->motoGroupRepository = $motoGroupRepository;
    }

    public function handle(Command $command, AutoMarka $autoMarka): MotoModel
    {
        $motoModel = new MotoModel($autoMarka, $this->motoGroupRepository->get($command->moto_groupID), $command->name);

        $this->repository->add($motoModel);

        $this->flusher->flush();

        return $motoModel;
    }
}
