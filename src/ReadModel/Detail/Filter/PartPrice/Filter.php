<?php


namespace App\ReadModel\Detail\Filter\PartPrice;


use App\Model\User\Entity\Opt\Opt;

class Filter
{
    public $number;
    public $optID;

    public function __construct()
    {
        $this->optID = Opt::DEFAULT_OPT_ID;
    }
}