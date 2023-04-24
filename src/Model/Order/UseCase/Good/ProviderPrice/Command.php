<?php

namespace App\Model\Order\UseCase\Good\ProviderPrice;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    public $zapSkladID;

    public $providerPriceID;

    public $isPrice;
}
