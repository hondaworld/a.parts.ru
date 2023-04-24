<?php

namespace App\Model\Detail\UseCase\Creater\Create;

use App\Model\Detail\Entity\Creater\Creater;
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

    public function handle(Command $command): Creater
    {
        $creater = new Creater(
            $command->name,
            $command->name_rus,
            $command->isOriginal,
            $command->tableName,
            $command->creater_weightID ? $this->createrRepository->get($command->creater_weightID) : null,
            $command->description
        );

        $this->createrRepository->add($creater);

        $this->flusher->flush();

        return $creater;
    }
}
