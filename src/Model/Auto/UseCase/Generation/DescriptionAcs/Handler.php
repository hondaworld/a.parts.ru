<?php

namespace App\Model\Auto\UseCase\Generation\DescriptionAcs;

use App\Model\Auto\Entity\Generation\AutoGenerationRepository;
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

    public function handle(Command $command): void
    {
        $autoGeneration = $this->repository->get($command->auto_generationID);

        $autoGeneration->getDescription()->updateAcs($command->acs);

        $this->flusher->flush();
    }
}
