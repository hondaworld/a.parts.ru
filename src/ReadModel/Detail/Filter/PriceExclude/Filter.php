<?php


namespace App\ReadModel\Detail\Filter\PriceExclude;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $number;
    public $createrID;
    public $providerPriceID;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/priceExclude')) {
            $filter = $session->get('filter/priceExclude');
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['createrID'])) $this->createrID = $filter['createrID'];
            if (isset($filter['providerPriceID'])) $this->providerPriceID = $filter['providerPriceID'];
        }
    }
}