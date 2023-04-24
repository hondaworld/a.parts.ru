<?php

namespace App\Model\Finance\UseCase\Currency\Create;

use App\Model\Finance\Entity\Currency\Currency;
use App\Model\Finance\Entity\Currency\CurrencyRepository;
use App\Model\Flusher;

class Handler
{
    private $currencies;
    private $flusher;

    public function __construct(CurrencyRepository $currencies, Flusher $flusher)
    {
        $this->currencies = $currencies;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $currency = new Currency(
            $command->code,
            $command->name_short,
            $command->name,
            $command->int_name,
            $command->int_1,
            $command->int_2,
            $command->int_5,
            $command->fract_name,
            $command->fract_1,
            $command->fract_2,
            $command->fract_5,
            $command->koef,
            $command->fix_rate,
            $command->is_fix_rate,
            $command->sex
        );

        $this->currencies->add($currency);

        $this->flusher->flush();
    }
}
