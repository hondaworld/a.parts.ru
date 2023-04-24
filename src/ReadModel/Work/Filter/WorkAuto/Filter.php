<?php


namespace App\ReadModel\Work\Filter\WorkAuto;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $auto_markaID;

    public function __construct()
    {
        if (isset($filter['auto_markaID'])) $this->auto_markaID = $filter['auto_markaID'];
    }
}