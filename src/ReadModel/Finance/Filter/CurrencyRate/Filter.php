<?php


namespace App\ReadModel\Finance\Filter\CurrencyRate;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $dateofadded;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/currencyRate')) {
            $filter = $session->get('filter/currencyRate');
            if (isset($filter['dateofadded'])) $this->dateofadded = $filter['dateofadded'];
        }
    }
}