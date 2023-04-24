<?php

namespace App\Model\Finance\UseCase\CurrencyRate\Edit;

use App\Model\Finance\Entity\CurrencyRate\CurrencyRate;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $currencyRateID;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d+$/",
     *     message="Значение должно быть целым числом"
     * )
     */
    public $numbers;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     */
    public $rate;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     */
    public $dateofadded;

    public $currency;

    public function __construct(int $currencyRateID)
    {
        $this->currencyRateID = $currencyRateID;
    }

    public static function fromCurrencyRate(CurrencyRate $currencyRate): self
    {
        $command = new self($currencyRate->getCurrencyRateID());
        $command->numbers = $currencyRate->getNumbers();
        $command->rate = $currencyRate->getRate();
        $command->dateofadded = $currencyRate->getDateofadded();
        $command->currency = $currencyRate->getCurrencyIDTo();
        return $command;
    }
}
