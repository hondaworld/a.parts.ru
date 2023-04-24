<?php

namespace App\Model\Card\UseCase\Card\Price;

use App\Model\Card\Entity\Card\ZapCard;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $zapCardID;

    public $price;

    public $currency_price;

    public $currency_providerPriceID;

    public function __construct(int $zapCardID)
    {
        $this->zapCardID = $zapCardID;
    }

    public static function fromEntity(ZapCard $zapCard): self
    {
        $command = new self($zapCard->getId());
        $command->price = $zapCard->getPrice();
        $command->currency_price = $zapCard->getCurrencyPrice();
        $command->currency_providerPriceID = $zapCard->getCurrencyProviderPrice() ? $zapCard->getCurrencyProviderPrice()->getId() : null;
        return $command;
    }
}
