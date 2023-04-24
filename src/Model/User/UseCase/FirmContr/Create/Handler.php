<?php

namespace App\Model\User\UseCase\FirmContr\Create;

use App\Model\Beznal\Entity\Bank\BankRepository;
use App\Model\Contact\Entity\Town\TownRepository;
use App\Model\Flusher;
use App\Model\User\Entity\FirmContr\Address;
use App\Model\User\Entity\FirmContr\FirmContr;
use App\Model\User\Entity\FirmContr\FirmContrRepository;
use App\Model\User\Entity\FirmContr\Ur;

class Handler
{
    private $firmContrRepository;
    private $flusher;
    private $townRepository;
    private $bankRepository;

    public function __construct(FirmContrRepository $firmContrRepository, TownRepository $townRepository, BankRepository $bankRepository, Flusher $flusher)
    {
        $this->firmContrRepository = $firmContrRepository;
        $this->flusher = $flusher;
        $this->townRepository = $townRepository;
        $this->bankRepository = $bankRepository;
    }

    public function handle(Command $command): void
    {
        $firmContr = new FirmContr(
            new Ur(
                $command->organization,
                $command->inn,
                $command->kpp,
                $command->okpo,
                $command->ogrn,
                $command->isNDS
            ),
            $this->townRepository->get($command->address->town->id),
            new Address(
                $command->address->zip,
                $command->address->street,
                $command->address->house,
                $command->address->str,
                $command->address->kv
            ),
            $command->phone, $command->fax, $command->email,
            $this->bankRepository->get($command->bank->id),
            $command->rasschet
        );

        $this->firmContrRepository->add($firmContr);

        $this->flusher->flush();
    }
}
