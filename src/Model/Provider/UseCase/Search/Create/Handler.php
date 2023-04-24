<?php

namespace App\Model\Provider\UseCase\Search\Create;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\ReadModel\Provider\PriceUploaderFetcher;

class Handler
{
    private PriceUploaderFetcher $priceUploaderFetcher;
    private CreaterRepository $createrRepository;

    public function __construct(PriceUploaderFetcher $priceUploaderFetcher, CreaterRepository $createrRepository)
    {
        $this->priceUploaderFetcher = $priceUploaderFetcher;
        $this->createrRepository = $createrRepository;
    }

    public function handle(Command $command): void
    {

        $creater = $this->createrRepository->get($command->createrID);

        $arr = [
            'number' => (new DetailNumber($command->number))->getValue(),
            'createrID' => $creater->getId(),
            'providerPriceID' => $command->providerPriceID,
            'price' => $command->price,
            'quantity' => $command->quantity ?: 0
        ];

        $this->priceUploaderFetcher->insertPrice($creater->getTableName(), $arr);
    }
}
