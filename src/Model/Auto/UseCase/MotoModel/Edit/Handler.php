<?php

namespace App\Model\Auto\UseCase\MotoModel\Edit;

use App\Model\Auto\Entity\MotoGroup\MotoGroupRepository;
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

    public function handle(Command $command): void
    {
        $motoModel = $this->repository->get($command->moto_modelID);

        $motoModel->update($this->motoGroupRepository->get($command->moto_groupID), $command->name);

        $this->flusher->flush();
    }
}
