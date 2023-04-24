<?php

namespace App\Model\Beznal\UseCase\Beznal\Edit;

use App\Model\Beznal\Entity\Bank\BankRepository;
use App\Model\Beznal\Entity\Beznal\BeznalRepository;
use App\Model\Flusher;

class Handler
{
    private $beznals;
    private $flusher;
    private $bankRepository;

    public function __construct(BeznalRepository $beznals, BankRepository $bankRepository, Flusher $flusher)
    {
        $this->beznals = $beznals;
        $this->flusher = $flusher;
        $this->bankRepository = $bankRepository;
    }

    public function handle(Command $command): void
    {

        $beznal = $this->beznals->get($command->beznalID);


        if ($command->manager) {
            $command->isMain = $command->manager->checkIsMainBeznal($command->isMain, $beznal);
        }

        if ($command->user) {
            $command->isMain = $command->user->checkIsMainBeznal($command->isMain, $beznal);
        }

        if ($command->firm) {
            $command->isMain = $command->firm->checkIsMainBeznal($command->isMain, $beznal);
        }

        $beznal->update(
            $this->bankRepository->get($command->bank->id), $command->rasschet, $command->description, $command->isMain
        );

        $this->flusher->flush();
    }
}
