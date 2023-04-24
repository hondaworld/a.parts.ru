<?php

namespace App\Model\Beznal\UseCase\Bank\Create;

use App\Model\Beznal\Entity\Bank\Bank;
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
        if ($this->bankRepository->hasByBik($command->bik)) {
            throw new \DomainException("Банк с таким БИК уже существует");
        }

        $bank = new Bank(
            $command->bik, $command->name, $command->korschet, $command->address, $command->description ?: ''
        );

        $this->bankRepository->add($bank);

        $this->flusher->flush();
    }
}
