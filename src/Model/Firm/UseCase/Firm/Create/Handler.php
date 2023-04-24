<?php

namespace App\Model\Firm\UseCase\Firm\Create;

use App\Model\Finance\Entity\Nalog\NalogRepository;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;

class Handler
{
    private $firmRepository;
    private $flusher;
    private $managerRepository;
    private $nalogRepository;

    public function __construct(FirmRepository $firmRepository, ManagerRepository $managerRepository, NalogRepository $nalogRepository, Flusher $flusher)
    {
        $this->firmRepository = $firmRepository;
        $this->flusher = $flusher;
        $this->managerRepository = $managerRepository;
        $this->nalogRepository = $nalogRepository;
    }

    public function handle(Command $command): void
    {
        $firm = new Firm(
            $command->name_short,
            $command->name,
            $command->inn,
            $command->kpp,
            $command->okpo,
            $command->ogrn,
            $command->isNDS,
            $command->isUr,
            $this->nalogRepository->get($command->nalogID),
            $command->directorID ? $this->managerRepository->get($command->directorID) : null,
            $command->buhgalterID ? $this->managerRepository->get($command->buhgalterID) : null
        );

        $this->firmRepository->add($firm);

        $this->flusher->flush();
    }
}
