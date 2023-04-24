<?php

namespace App\ReadModel\Analytics\UseCase\ComparePrice;

use App\Model\Card\Entity\Stock\ZapCardStock;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var array
     */
    public $providerPriceID;

    /**
     * @var float
     */
    public $profit = 0;

    /**
     * @var string
     */
    public $file;

    /**
     * @var int
     */
    public $days;

    public $isExcel = false;
}
