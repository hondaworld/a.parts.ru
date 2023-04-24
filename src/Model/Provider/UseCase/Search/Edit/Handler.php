<?php

namespace App\Model\Provider\UseCase\Search\Edit;

use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Flusher;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\ReadModel\Provider\PriceUploaderFetcher;

class Handler
{
    private $flusher;
    private $priceUploaderFetcher;

    public function __construct(PriceUploaderFetcher $priceUploaderFetcher, Flusher $flusher)
    {
        $this->flusher = $flusher;
        $this->priceUploaderFetcher = $priceUploaderFetcher;
    }

    public function handle(Command $command, string $number, ProviderPrice $providerPrice, Creater $creater): void
    {
        $this->priceUploaderFetcher->updatePrice($creater->getTableName(), $number, $creater->getId(), $providerPrice->getId(), str_replace(',', '.', $command->price), $command->quantity ?: 0);

        $this->flusher->flush();
    }
}
