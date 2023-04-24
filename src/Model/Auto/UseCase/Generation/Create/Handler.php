<?php

namespace App\Model\Auto\UseCase\Generation\Create;

use App\Model\Auto\Entity\Generation\AutoGeneration;
use App\Model\Auto\Entity\Generation\AutoGenerationRepository;
use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(AutoGenerationRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command, AutoModel $autoModel): AutoGeneration
    {
        $autoGeneration = new AutoGeneration($autoModel, $command->name, $command->name_rus, $command->yearfrom, $command->yearto);

        $this->repository->add($autoGeneration);

        $this->flusher->flush();

        return $autoGeneration;
    }
}
