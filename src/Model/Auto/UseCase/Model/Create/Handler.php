<?php

namespace App\Model\Auto\UseCase\Model\Create;

use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Auto\Entity\Model\AutoModelRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(AutoModelRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command, AutoMarka $autoMarka): AutoModel
    {
        $autoModel = new AutoModel($autoMarka, $command->name, $command->name_rus, $command->path);

        $this->repository->add($autoModel);

        $this->flusher->flush();

        return $autoModel;
    }
}
