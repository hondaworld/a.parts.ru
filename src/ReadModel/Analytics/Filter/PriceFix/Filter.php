<?php


namespace App\ReadModel\Analytics\Filter\PriceFix;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $price_groupID;
    public $is_price_group_fix;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/analyticsPriceFix')) {
            $filter = $session->get('filter/analyticsPriceFix');
            if (isset($filter['price_groupID'])) $this->price_groupID = $filter['price_groupID'];
            if (isset($filter['is_price_group_fix'])) $this->is_price_group_fix = $filter['is_price_group_fix'];
        }
    }
}