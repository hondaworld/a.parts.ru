<?php

namespace App\Model\Detail\UseCase\PartsPrice\Price;

use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Flusher;
use App\ReadModel\Provider\PriceUploaderFetcher;

class Handler
{
    private $priceUploaderFetcher;
    private CreaterRepository $createrRepository;

    public function __construct(PriceUploaderFetcher $priceUploaderFetcher, CreaterRepository $createrRepository)
    {
        $this->priceUploaderFetcher = $priceUploaderFetcher;
        $this->createrRepository = $createrRepository;
    }

    public function handle(Command $command): void
    {
        $creater = $this->createrRepository->get($command->createrID);

        $this->priceUploaderFetcher->updatePrice($creater->getTableName(), $command->number, $creater->getId(), $command->providerPriceID, str_replace(',', '.', $command->price));

    }
}
