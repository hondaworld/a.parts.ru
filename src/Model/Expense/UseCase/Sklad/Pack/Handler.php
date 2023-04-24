<?php

namespace App\Model\Expense\UseCase\Sklad\Pack;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Expense\Entity\Sklad\ExpenseSkladRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;

class Handler
{
    private Flusher $flusher;
    private ExpenseSkladRepository $expenseSkladRepository;
    private ZapSkladRepository $zapSkladRepository;
    private ZapCardRepository $zapCardRepository;
    private ManagerRepository $managerRepository;

    public function __construct(
        ExpenseSkladRepository $expenseSkladRepository,
        ZapCardRepository      $zapCardRepository,
        ZapSkladRepository     $zapSkladRepository,
        ManagerRepository      $managerRepository,
        Flusher                $flusher
    )
    {
        $this->flusher = $flusher;
        $this->expenseSkladRepository = $expenseSkladRepository;
        $this->zapSkladRepository = $zapSkladRepository;
        $this->zapCardRepository = $zapCardRepository;
        $this->managerRepository = $managerRepository;
    }

    public function handle(Command $command, ZapSklad $zapSklad): array
    {
        $messages = [];

        if (!$command->isDelete && !$command->managerID) {
            throw new \DomainException('Выберите, пожалуйста, сборщика');
        }

        foreach ($command->cols as $id) {
            $arr = explode('_', $id);
            $zapCard = $this->zapCardRepository->get($arr[0]);
            $zapSklad_to = $this->zapSkladRepository->get($arr[1]);

            if ($command->isDelete) {
                $expenseSklads = $this->expenseSkladRepository->findPacked($zapCard, $zapSklad, $zapSklad_to);
                foreach ($expenseSklads as $expenseSklad) {
                    $expenseSklad->unPack();
                }
            } else {
                $expenseSklads = $this->expenseSkladRepository->findAdded($zapCard, $zapSklad, $zapSklad_to);
                foreach ($expenseSklads as $expenseSklad) {
                    $expenseSklad->pack($this->managerRepository->get($command->managerID));
                }
            }
        }

        $this->flusher->flush();
        return $messages;
    }
}
