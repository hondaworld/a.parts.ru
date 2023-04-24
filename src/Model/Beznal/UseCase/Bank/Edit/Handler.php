<?php

namespace App\Model\Beznal\UseCase\Bank\Edit;

use App\Model\Beznal\Entity\Bank\BankRepository;
use App\Model\Flusher;

class Handler
{
    private $flusher;
    private $bankRepository;

    public function __construct(BankRepository $bankRepository, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->bankRepository = $bankRepository;
    }

    public function handle(Command $command): void
    {
        $bank = $this->bankRepository->get($command->bankID);

        if ($this->bankRepository->hasByBik($command->bik, $command->bankID)) {
            throw new \DomainException("Банк с таким БИК уже существует");
        }

        $bank->update(
            $command->bik, $command->name, $command->korschet, $command->address, $command->description ?: ''
        );

        $this->flusher->flush();
    }
}
