<?php

namespace App\Model\Auto\UseCase\Marka\Create;

use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Auto\Entity\Marka\AutoMarkaRepository;
use App\Model\Flusher;

class Handler
{
    private $repository;
    private $flusher;

    public function __construct(AutoMarkaRepository $repository, Flusher $flusher)
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $autoMarka = new AutoMarka($command->name, $command->name_rus);

        $this->repository->add($autoMarka);

        $this->flusher->flush();
    }
}
