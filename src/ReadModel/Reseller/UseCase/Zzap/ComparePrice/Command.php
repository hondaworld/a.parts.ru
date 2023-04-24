<?php

namespace App\ReadModel\Reseller\UseCase\Zzap\ComparePrice;

use App\Model\Card\Entity\Stock\ZapCardStock;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     */
    public $file;
}
