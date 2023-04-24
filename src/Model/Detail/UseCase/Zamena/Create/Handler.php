<?php

namespace App\Model\Detail\UseCase\Zamena\Create;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Detail\Entity\Zamena\ShopZamena;
use App\Model\Detail\Entity\Zamena\ShopZamenaRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\ReadModel\Detail\ShopZamenaFetcher;

class Handler
{
    private $repository;
    private $flusher;
    private CreaterRepository $createrRepository;
    private ShopZamenaFetcher $shopZamenaFetcher;

    public function __construct(
        ShopZamenaRepository $repository,
        CreaterRepository $createrRepository,
        ShopZamenaFetcher $shopZamenaFetcher,
        Flusher $flusher
    )
    {
        $this->repository = $repository;
        $this->flusher = $flusher;
        $this->createrRepository = $createrRepository;
        $this->shopZamenaFetcher = $shopZamenaFetcher;
    }

    public function handle(Command $command, Manager $manager): void
    {
        $number = new DetailNumber($command->number);
        $number2 = new DetailNumber($command->number2);

        if (!$this->shopZamenaFetcher->hasZamena(
                $number->getValue(),
                $command->createrID,
                $number2->getValue(),
                $command->createrID2
            ) &&
            !($number->isEqual($number2) && $command->createrID == $command->createrID2)
        ) {
            $shopZamena = new ShopZamena(
                $number,
                $this->createrRepository->get($command->createrID),
                $number2,
                $this->createrRepository->get($command->createrID2),
                $manager
            );

            $this->repository->add($shopZamena);
        }

        $this->flusher->flush();
    }
}
