<?php

namespace App\Model\Beznal\UseCase\Beznal\Create;

use App\Model\Beznal\Entity\Bank\BankRepository;
use App\Model\Beznal\Entity\Beznal\Beznal;
use App\Model\Flusher;

class Handler
{
    private Flusher $flusher;
    private BankRepository $bankRepository;

    public function __construct(BankRepository $bankRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->bankRepository = $bankRepository;
    }

    public function handle(Command $command): void
    {
        $object = null;

        if ($command->manager) {
            $command->isMain = $command->manager->checkIsMainBeznal($command->isMain);
            $object = $command->manager;
        }

        if ($command->user) {
            $command->isMain = $command->user->checkIsMainBeznal($command->isMain);
            $object = $command->user;
        }

        if ($command->firm) {
            $command->isMain = $command->firm->checkIsMainBeznal($command->isMain);
            $object = $command->firm;
        }

        $beznal = new Beznal(
            $object, $this->bankRepository->get($command->bank->id), $command->rasschet, $command->description, $command->isMain
        );
        $object->assignBeznal($beznal);

        $this->flusher->flush();
    }
}
