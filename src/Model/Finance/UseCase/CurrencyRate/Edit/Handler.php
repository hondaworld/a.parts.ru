<?php

namespace App\Model\Finance\UseCase\CurrencyRate\Edit;

use App\Model\Finance\Entity\Currency\Currency;
use App\Model\Finance\Entity\Currency\CurrencyRepository;
use App\Model\Finance\Entity\CurrencyRate\CurrencyRate;
use App\Model\Finance\Entity\CurrencyRate\CurrencyRateRepository;
use App\Model\Flusher;

class Handler
{
    private $rates;
    private $currencies;
    private $flusher;

    public function __construct(CurrencyRateRepository $rates, CurrencyRepository $currencies, Flusher $flusher)
    {
        $this->rates = $rates;
        $this->currencies = $currencies;
        $this->flusher = $flusher;
    }

    public function handle(Command $command): void
    {
        $currency_from = $this->currencies->getCurrencyNational();

        if ($this->rates->hasByDate($command->currency, $currency_from, $command->dateofadded, $command->currencyRateID)) {
            throw new \DomainException('Данные за эту дату уже есть.');
        }

        $currencyRate = $this->rates->get($command->currencyRateID);

        $currencyRate->update($command->currency, $currency_from, $command->rate, $command->dateofadded, $command->numbers);

        $this->flusher->flush();
    }
}
