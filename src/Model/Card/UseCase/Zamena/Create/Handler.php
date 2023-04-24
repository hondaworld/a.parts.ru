<?php

namespace App\Model\Card\UseCase\Zamena\Create;

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
        $arrNumbers = explode("\n", $command->number);
        foreach ($arrNumbers as $arrNumber) {
            $number = new DetailNumber($arrNumber);
            if ($number->getValue() != '') {

                if (!$this->shopZamenaFetcher->hasZamena(
                    $command->zapCard->getNumber()->getValue(),
                    $command->zapCard->getCreater()->getId(),
                    $number->getValue(),
                    $command->createrID) &&
                    !($command->zapCard->getNumber()->isEqual($number) && $command->zapCard->getCreater()->getId() == $command->createrID)
                ) {
                    $shopZamena = new ShopZamena(
                        $command->zapCard->getNumber(),
                        $command->zapCard->getCreater(),
                        $number,
                        $this->createrRepository->get($command->createrID),
                        $manager
                    );

                    $this->repository->add($shopZamena);
                }

            }
        }

        $this->flusher->flush();
    }
}
