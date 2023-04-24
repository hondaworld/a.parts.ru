<?php

namespace App\Model\Sklad\UseCase\ZapSklad\Edit;

use App\Model\Finance\Entity\Currency\CurrencyRepository;
use App\Model\Flusher;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\Model\User\Entity\Opt\OptRepository;

class Handler
{
    private $zapSkladRepository;
    private $optRepository;
    private $flusher;

    public function __construct(ZapSkladRepository $zapSkladRepository, OptRepository $optRepository, Flusher $flusher)
    {
        $this->zapSkladRepository = $zapSkladRepository;
        $this->optRepository = $optRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $zapSklad = $this->zapSkladRepository->get($command->zapSkladID);

        if ($command->isMain && !$zapSklad->isMain()) {
            $this->zapSkladRepository->updateMain();
        }

        $zapSklad->update(
            $command->name_short,
            $command->name,
            $command->isTorg,
            $command->koef,
            $command->optID ? $this->optRepository->get($command->optID) : null,
            $command->isMain
        );

        $this->flusher->flush();
    }
}
