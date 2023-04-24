<?php

namespace App\Model\Card\UseCase\Auto\Create;

use App\Model\Auto\Entity\Model\AutoModelRepository;
use App\Model\Auto\Entity\MotoModel\MotoModelRepository;
use App\Model\Flusher;

class Handler
{
    private Flusher $flusher;
    private AutoModelRepository $autoModelRepository;
    private MotoModelRepository $motoModelRepository;

    public function __construct(
        AutoModelRepository $autoModelRepository,
        MotoModelRepository $motoModelRepository,
        Flusher $flusher
    )
    {
        $this->flusher = $flusher;
        $this->autoModelRepository = $autoModelRepository;
        $this->motoModelRepository = $motoModelRepository;
    }

    public function handle(Command $command): void
    {
        $arrYears = explode("\n", $command->year);
        foreach ($arrYears as $year) {
            $year = intval(trim($year));
            if ($year != 0) {
                $command->zapCard->assignZapCardAuto(
                    $command->auto_modelID ? $this->autoModelRepository->get($command->auto_modelID) : null,
                    $command->moto_modelID ? $this->motoModelRepository->get($command->moto_modelID) : null,
                    $year
                );
            }
        }

        $this->flusher->flush();
    }
}
