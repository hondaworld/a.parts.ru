<?php


namespace App\ReadModel\Order\Filter\Shippings;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $inPage;
    public $user_name;
    public $gruz_firm_name;
    public $gruz_user_town;
    public $pay_type_name;
    public $delivery_tkID;
    public $tracknumber;
    public $status;
    public $dateofadded;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/shippings')) {
            $filter = $session->get('filter/shippings');
            if (isset($filter['user_name'])) $this->user_name = $filter['user_name'];
            if (isset($filter['gruz_firm_name'])) $this->gruz_firm_name = $filter['gruz_firm_name'];
            if (isset($filter['gruz_user_town'])) $this->gruz_user_town = $filter['gruz_user_town'];
            if (isset($filter['pay_type_name'])) $this->pay_type_name = $filter['pay_type_name'];
            if (isset($filter['delivery_tkID'])) $this->delivery_tkID = $filter['delivery_tkID'];
            if (isset($filter['tracknumber'])) $this->tracknumber = $filter['tracknumber'];
            if (isset($filter['status'])) $this->status = $filter['status'];
            if (isset($filter['dateofadded'])) $this->dateofadded = $filter['dateofadded'];
        }
    }
}