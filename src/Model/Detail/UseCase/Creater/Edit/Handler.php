<?php

namespace App\Model\Detail\UseCase\Creater\Edit;

use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Flusher;

class Handler
{
    private $flusher;
    private $createrRepository;

    public function __construct(CreaterRepository $createrRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
    }

    public function handle(Command $command): void
    {
        $creater = $this->createrRepository->get($command->createrID);

        $creater->update(
            $command->name,
            $command->name_rus,
            $command->isOriginal,
            $command->tableName,
            $command->creater_weightID ? $this->createrRepository->get($command->creater_weightID) : null,
            $command->description,
            $command->catalogs,
            $command->alt_names
        );

        $this->flusher->flush();
    }
}
