<?php

namespace App\Model\Order\UseCase\Good\CreateCustom;

use App\Model\Order\Entity\Order\Order;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    public $providerPriceID;

    public $createrID;
}
