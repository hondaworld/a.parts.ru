<?php

namespace App\Model\Provider\UseCase\Price\Num;

use App\Model\Flusher;
use App\Model\Provider\Entity\Price\Num;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;

class Handler
{
    private ProviderPriceRepository $providerPriceRepository;
    private Flusher $flusher;

    public function __construct(
        ProviderPriceRepository $providerPriceRepository,
        Flusher $flusher
    )
    {
        $this->providerPriceRepository = $providerPriceRepository;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $providerPrice = $this->providerPriceRepository->get($command->providerPriceID);

        $nums = [];
        foreach ($command->fields as $key => $field) {
            if ($field != null) {
                $nums[$field] = $key;
            }
        }

        $num = new Num(
            $nums['creater'] ?? null,
            $nums['number'] ?? null,
            $nums['price'] ?? null,
            $nums['quantity'] ?? null,
            $nums['name'] ?? null,
            $nums['rg'] ?? null,
            $nums['creater_add'] ?? null,
        );

        $providerPrice->updateNum($num);

        $this->flusher->flush();
    }
}
