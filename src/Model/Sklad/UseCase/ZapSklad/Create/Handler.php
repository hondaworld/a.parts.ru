<?php

namespace App\Model\Sklad\UseCase\ZapSklad\Create;

use App\Model\Flusher;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\Model\User\Entity\Opt\OptRepository;

class Handler
{
    private $zapSkladRepository;
    private $flusher;
    private $optRepository;

    public function __construct(ZapSkladRepository $zapSkladRepository, OptRepository $optRepository, Flusher $flusher)
    {
        $this->zapSkladRepository = $zapSkladRepository;
        $this->flusher = $flusher;
        $this->optRepository = $optRepository;
    }

    public function handle(Command $command): void
    {
        $zapSklad = new ZapSklad(
            $command->name_short,
            $command->name,
            $command->isTorg,
            $command->koef,
            $command->optID ? $this->optRepository->get($command->optID) : null,
            $command->isMain
        );

        if ($command->isMain) {
            $this->zapSkladRepository->updateMain();
        }

        $this->zapSkladRepository->add($zapSklad);

        $this->flusher->flush();
    }
}
