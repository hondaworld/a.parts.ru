<?php


namespace App\ReadModel\Order\Filter\Order;


use Symfony\Component\HttpFoundation\Session\Session;

class Filter
{
    public $param;
    public $orderID;
    public $number;
    public $createrID;
    public $user;

    public function __construct()
    {
        $session = new Session();
        if ($session->get('filter/orders')) {
            $filter = $session->get('filter/orders');
            if (isset($filter['param'])) $this->param = $filter['param'];
            if (isset($filter['orderID'])) $this->orderID = $filter['orderID'];
            if (isset($filter['number'])) $this->number = $filter['number'];
            if (isset($filter['createrID'])) $this->createrID = $filter['createrID'];
            if (isset($filter['user'])) $this->user = $filter['user'];
        }
    }
}